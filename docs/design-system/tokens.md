# HD Tickets Design System - Design Tokens

A comprehensive design token system that provides a single source of truth for all design decisions in the HD Tickets platform.

## Table of Contents

1. [Overview](#overview)
2. [Token Architecture](#token-architecture)
3. [Naming Conventions](#naming-conventions)
4. [Token Categories](#token-categories)
5. [Usage in Components](#usage-in-components)
6. [Theming](#theming)
7. [Tailwind Integration](#tailwind-integration)
8. [Migration Guide](#migration-guide)

## Overview

Design tokens are the atomic values that define the visual properties of our design system. They ensure consistency across all user interfaces and enable theming, accessibility, and maintainable code.

### Benefits

- **Consistency**: Single source of truth for design decisions
- **Maintainability**: Easy to update values across the entire platform
- **Theming**: Support for light/dark mode and role-based themes
- **Accessibility**: Built-in support for reduced motion and high contrast
- **Scalability**: Easy to extend and modify as the system grows

## Token Architecture

Our design tokens follow a two-tier architecture:

### 1. Primitive Tokens
Raw values that define the basic building blocks of our design system.

```css
/* Color primitives */
--hdt-blue-500: #3b82f6;
--hdt-gray-900: #111827;

/* Spacing primitives */
--hdt-spacing-4: 1rem;
--hdt-spacing-8: 2rem;

/* Typography primitives */
--hdt-font-size-lg: clamp(1.125rem, 1rem + 0.625vw, 1.25rem);
```

### 2. Semantic Tokens
Contextual tokens that reference primitive tokens and provide meaning to design decisions.

```css
/* Semantic color assignments */
--hdt-color-primary-600: var(--hdt-blue-600);
--hdt-color-text-primary: var(--hdt-gray-900);
--hdt-color-surface-secondary: #ffffff;
```

## Naming Conventions

### Token Naming Pattern

All design tokens follow this pattern:
```
--hdt-{category}-{property}-{variant}-{state}
```

**Examples:**
```css
--hdt-color-primary-600          /* Color category, primary property, 600 variant */
--hdt-spacing-4                  /* Spacing category, 4 variant */
--hdt-font-size-lg              /* Typography category, font-size property, lg variant */
--hdt-shadow-md                 /* Shadow category, md variant */
```

### Prefix Convention

- `hdt` = HD Tickets design system namespace
- Prevents conflicts with other CSS variables
- Makes tokens easily identifiable in code

### Variant Scale

Most token categories use a consistent scale:

- **Numeric Scale**: `50, 100, 200...900, 950` (for colors)
- **T-shirt Scale**: `xs, sm, base/md, lg, xl, 2xl, 3xl` (for sizes)
- **Semantic Scale**: `primary, secondary, tertiary, quaternary` (for hierarchy)

## Token Categories

### Colors

#### Primitive Colors
Complete color palettes with consistent scales:
```css
--hdt-gray-50: #f9fafb;
--hdt-gray-100: #f3f4f6;
/* ... through ... */
--hdt-gray-900: #111827;
--hdt-gray-950: #030712;
```

#### Semantic Colors
Contextual color assignments:
```css
/* Brand colors */
--hdt-color-primary-600: var(--hdt-blue-600);
--hdt-color-secondary-600: var(--hdt-cyan-600);

/* Status colors */
--hdt-color-success-500: var(--hdt-green-500);
--hdt-color-warning-500: var(--hdt-yellow-500);
--hdt-color-danger-500: var(--hdt-red-500);
--hdt-color-info-500: var(--hdt-blue-500);
```

#### Surface Colors
Background and surface treatments:
```css
--hdt-color-surface-primary: var(--hdt-gray-50);    /* Page background */
--hdt-color-surface-secondary: #ffffff;              /* Card background */
--hdt-color-surface-tertiary: var(--hdt-gray-100);  /* Subtle backgrounds */
```

#### Text Colors
Text color hierarchy:
```css
--hdt-color-text-primary: var(--hdt-gray-900);      /* Primary headings, body */
--hdt-color-text-secondary: var(--hdt-gray-700);    /* Secondary text */
--hdt-color-text-tertiary: var(--hdt-gray-600);     /* Muted text */
--hdt-color-text-quaternary: var(--hdt-gray-500);   /* Disabled, placeholder */
```

### Typography

#### Font Families
```css
--hdt-font-family-sans: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji";
--hdt-font-family-serif: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
--hdt-font-family-mono: ui-monospace, SFMono-Regular, "SF Mono", Consolas;
```

#### Fluid Typography Scale
Using `clamp()` for responsive typography:
```css
--hdt-font-size-xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);
--hdt-font-size-sm: clamp(0.875rem, 0.8rem + 0.375vw, 1rem);
--hdt-font-size-base: clamp(1rem, 0.9rem + 0.5vw, 1.125rem);
--hdt-font-size-lg: clamp(1.125rem, 1rem + 0.625vw, 1.25rem);
```

#### Line Heights
```css
--hdt-line-height-tight: 1.25;
--hdt-line-height-snug: 1.375;
--hdt-line-height-normal: 1.5;
--hdt-line-height-relaxed: 1.625;
--hdt-line-height-loose: 2;
```

#### Font Weights
```css
--hdt-font-weight-normal: 400;
--hdt-font-weight-medium: 500;
--hdt-font-weight-semibold: 600;
--hdt-font-weight-bold: 700;
```

### Spacing

#### 8px Baseline Grid
All spacing follows an 8px baseline grid:
```css
--hdt-spacing-1: 0.25rem;    /* 4px */
--hdt-spacing-2: 0.5rem;     /* 8px */
--hdt-spacing-3: 0.75rem;    /* 12px */
--hdt-spacing-4: 1rem;       /* 16px */
--hdt-spacing-6: 1.5rem;     /* 24px */
--hdt-spacing-8: 2rem;       /* 32px */
```

### Border Radius
```css
--hdt-radius-sm: 0.125rem;   /* 2px */
--hdt-radius-base: 0.25rem;  /* 4px */
--hdt-radius-md: 0.375rem;   /* 6px */
--hdt-radius-lg: 0.5rem;     /* 8px */
--hdt-radius-xl: 0.75rem;    /* 12px */
--hdt-radius-full: 9999px;
```

### Shadows (Elevation)
```css
--hdt-shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
--hdt-shadow-base: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
--hdt-shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
--hdt-shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
```

### Motion & Transitions
```css
--hdt-duration-150: 150ms;
--hdt-duration-300: 300ms;
--hdt-duration-500: 500ms;

--hdt-ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
--hdt-ease-out: cubic-bezier(0, 0, 0.2, 1);
```

## Usage in Components

### In CSS
```css
.hdt-button {
  padding: var(--hdt-spacing-2) var(--hdt-spacing-4);
  font-size: var(--hdt-font-size-base);
  border-radius: var(--hdt-radius-md);
  transition-duration: var(--hdt-duration-150);
  box-shadow: var(--hdt-shadow-sm);
}

.hdt-button--primary {
  background-color: var(--hdt-color-primary-600);
  color: var(--hdt-color-text-inverse);
}
```

### In Tailwind Classes
```html
<button class="bg-hd-primary-600 text-white px-4 py-2 rounded-md shadow-sm transition-150">
  Primary Button
</button>
```

### Utility Classes
```html
<div class="hdt-surface-secondary hdt-spacing-responsive">
  <h1 class="hdt-heading-lg hdt-role-primary">Dashboard</h1>
  <p class="hdt-body-base">Welcome to your dashboard</p>
</div>
```

## Theming

### Dark Mode Support
All tokens automatically support dark mode through CSS custom properties:

```css
:root {
  --hdt-color-surface-primary: var(--hdt-gray-50);   /* Light mode */
  --hdt-color-text-primary: var(--hdt-gray-900);
}

.dark {
  --hdt-color-surface-primary: var(--hdt-gray-900);  /* Dark mode */
  --hdt-color-text-primary: var(--hdt-gray-50);
}
```

### Role-Based Theming
Each user role has its own color accent system:

```css
/* Admin theme */
.admin-layout {
  --hdt-role-primary: var(--hdt-color-admin-primary);
  --hdt-role-secondary: var(--hdt-color-admin-secondary);
  --hdt-role-surface: var(--hdt-color-admin-surface);
}

/* Agent theme */
.agent-layout {
  --hdt-role-primary: var(--hdt-color-agent-primary);
  --hdt-role-secondary: var(--hdt-color-agent-secondary);
  --hdt-role-surface: var(--hdt-color-agent-surface);
}
```

### Accessibility Support
Tokens automatically respect user preferences:

```css
@media (prefers-reduced-motion: reduce) {
  :root {
    --hdt-duration-150: 0ms;
    --hdt-duration-300: 0ms;
    --hdt-duration-500: 0ms;
  }
}
```

## Tailwind Integration

Our design tokens are fully integrated with Tailwind CSS:

### Color Classes
```html
<!-- Using semantic tokens -->
<div class="bg-surface-secondary text-text-primary border-border-primary">
  
<!-- Using brand tokens -->
<button class="bg-hd-primary-600 hover:bg-hd-primary-700">
  
<!-- Using role tokens -->
<div class="bg-role-surface text-role-primary border-role-primary">
```

### Spacing Classes
```html
<div class="p-4 m-6 gap-8">  <!-- Uses design token spacing -->
```

### Typography Classes
```html
<h1 class="text-4xl font-bold leading-tight">  <!-- Uses token typography scale -->
```

## Migration Guide

### From Legacy System

1. **Replace hardcoded values:**
```css
/* Before */
.button {
  background-color: #3b82f6;
  padding: 8px 16px;
  border-radius: 6px;
}

/* After */
.button {
  background-color: var(--hdt-color-primary-600);
  padding: var(--hdt-spacing-2) var(--hdt-spacing-4);
  border-radius: var(--hdt-radius-md);
}
```

2. **Update component styles:**
```css
/* Replace uiv2 classes with hdt classes */
.uiv2-button-primary → .hdt-button--primary
.uiv2-card-elevated → .hdt-card--elevated
```

3. **Use Tailwind token classes:**
```html
<!-- Replace arbitrary values with token classes -->
<div class="bg-[#f3f4f6]">  <!-- Before -->
<div class="bg-hd-gray-100"> <!-- After -->
```

### Best Practices

1. **Always use semantic tokens** when possible:
```css
/* Preferred */
color: var(--hdt-color-text-secondary);

/* Avoid */
color: var(--hdt-gray-700);
```

2. **Use role tokens** for role-specific theming:
```css
.dashboard-header {
  background: var(--hdt-role-surface);
  color: var(--hdt-role-primary);
}
```

3. **Respect motion preferences:**
```css
.animated-element {
  transition-duration: var(--hdt-duration-300);
  /* Will automatically be 0ms if user prefers reduced motion */
}
```

4. **Follow naming conventions** when creating custom tokens:
```css
/* Good */
--hdt-component-property-variant

/* Avoid */
--custom-blue-color
--myComponentPadding
```

## Development Workflow

### Adding New Tokens

1. **Determine if primitive or semantic:**
   - Primitive: Raw value (color hex, pixel value, etc.)
   - Semantic: References primitive and provides context

2. **Follow naming convention:**
   ```css
   --hdt-{category}-{property}-{variant}
   ```

3. **Add to appropriate layer:**
   ```css
   @layer base {
     :root {
       --hdt-color-accent-500: #8b5cf6; /* New primitive */
     }
   }
   ```

4. **Update Tailwind config** if needed:
   ```js
   colors: {
     'hd-accent': {
       500: 'var(--hdt-color-accent-500)',
     }
   }
   ```

5. **Document usage** in this file and component documentation.

### Token Validation

Tokens should be validated for:
- **Accessibility**: Color contrast ratios, motion preferences
- **Consistency**: Follows established patterns and scales
- **Performance**: Doesn't create excessive cascade complexity
- **Maintainability**: Clear naming and logical grouping

## Tools and Resources

- **Token Inspector**: Browser dev tools for inspecting computed token values
- **Contrast Checker**: Ensure color combinations meet WCAG guidelines
- **Design Token Validator**: Custom build tools to validate token usage
- **Documentation**: This file and component-specific documentation

---

For component-specific token usage, see the individual component documentation in `/docs/design-system/components/`.

For implementation details, see `/resources/css/design-tokens.css` and `tailwind.config.js`.
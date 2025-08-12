# HD Tickets - Unified Layout System Architecture

## Overview

The HD Tickets Unified Layout System provides a single, flexible master layout that handles all device types with progressive enhancement and role-based variants. Built with mobile-first responsive design principles using CSS Grid and Flexbox.

## Features

- ✅ **Single Master Layout**: One layout handles all device types
- ✅ **Mobile-First Design**: Progressive enhancement for larger screens
- ✅ **Role-Based Variants**: Admin, Agent, and Customer layout variations
- ✅ **CSS Grid & Flexbox**: Modern responsive layout techniques
- ✅ **Blade Template Inheritance**: Clean @extends and @section structure
- ✅ **Dark Mode Support**: Complete theme switching capability
- ✅ **Accessibility**: WCAG compliant with screen reader support
- ✅ **Performance Optimized**: Minimal CSS, optimized assets

## Architecture Components

### 1. Master Layout (`layouts/master.blade.php`)

The central layout template that all pages extend. Features:

- Responsive HTML5 structure
- Dynamic role-based CSS classes
- Alpine.js integration for interactivity
- Progressive Web App (PWA) ready
- SEO optimized meta tags

```blade
@extends('layouts.master')
@section('title', 'Your Page Title')
@section('content')
    <!-- Your page content -->
@endsection
```

### 2. Layout Regions

#### Header (`layouts/partials/header.blade.php`)
- Responsive navigation
- User authentication status
- Role-based menu items
- Search functionality
- Theme toggle
- Mobile-friendly hamburger menu

#### Sidebar (`layouts/partials/sidebar.blade.php`)
- Role-based navigation menu
- Collapsible on desktop
- Overlay on mobile/tablet
- Real-time status indicators
- Quick stats display

#### Main Content (`layout-main`)
- Flexible content area
- Auto-scrolling
- Flash message display
- Breadcrumb support

#### Footer (`layouts/partials/footer.blade.php`)
- Copyright information
- Legal links
- System status
- Version information

### 3. CSS Grid System (`layout-system.css`)

#### Responsive Breakpoints
```css
--mobile-max: 767px
--tablet-min: 768px (to 1023px)  
--desktop-min: 1024px
--desktop-large-min: 1200px
--desktop-xl-min: 1440px
```

#### Grid Template Areas

**Mobile (< 768px)**
```
"header"
"main"  
"footer"
```

**Tablet & Desktop (≥ 768px)**
```
"header header"
"sidebar main"
"footer footer"
```

## Role-Based Layout Variants

### Admin Layout
- **Full-featured interface** with complete system access
- **Gradient sidebar**: Purple/blue gradient background
- **Administrative tools** in navigation
- **System monitoring** widgets
- **User management** capabilities

### Agent Layout  
- **Task-focused interface** for ticket monitoring
- **Cyan gradient sidebar**: Blue/teal gradient background
- **Ticket management** tools
- **Alert system** integration
- **Performance metrics** dashboard

### Customer Layout
- **Clean, simple interface** for end users
- **No sidebar** on smaller screens
- **Green accent color** scheme
- **Focused on ticket search** and watchlist
- **Minimal navigation** for better UX

## Usage Examples

### Basic Page Structure

```blade
@extends('layouts.master')

@section('title', 'Dashboard')

@section('page-header')
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <button class="btn-primary">Action</button>
    </div>
@endsection

@section('content')
    <div class="card-grid">
        <div class="main-section">
            <p>Your content here</p>
        </div>
    </div>
@endsection
```

### Adding Custom Styles

```blade
@push('head')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@endpush
```

### Adding JavaScript

```blade
@push('scripts')
<script>
    // Global scripts
</script>
@endpush

@section('javascript')
<script>
    // Page-specific scripts
</script>
@endsection
```

## CSS Utility Classes

### Layout Utilities
```css
.layout-grid           /* Main grid container */
.layout-header         /* Header region */
.layout-sidebar        /* Sidebar region */  
.layout-main          /* Main content region */
.layout-footer        /* Footer region */
```

### Content Utilities
```css
.content-wrapper       /* Responsive padding container */
.main-section         /* Content card with styling */
.card-grid            /* Responsive grid for cards */
.padding-responsive   /* Progressive padding system */
```

### Component Utilities
```css
.btn-primary          /* Primary button style */
.btn-secondary        /* Secondary button style */
.badge                /* Status badge */
.alert                /* Alert message */
.form-input           /* Form input styling */
```

### Status Indicators
```css
.status-online        /* Green online indicator */
.status-offline       /* Red offline indicator */
.status-pending       /* Yellow pending indicator */
.real-time-indicator  /* Live status with pulse */
```

## Responsive Behavior

### Mobile (≤ 767px)
- Single column layout
- Header with hamburger menu
- Sidebar becomes slide-out overlay
- Full-width content cards
- Touch-friendly 44px minimum targets
- Optimized font sizes and spacing

### Tablet (768px - 1023px)  
- Two-column layout (sidebar + main)
- Sidebar visible for admin/agent roles
- Customer role remains single column
- Intermediate padding and sizing

### Desktop (≥ 1024px)
- Full sidebar with collapse option
- Larger content areas
- Hover states and interactions
- Maximum content width constraints

## Theme System

### Light Mode (Default)
- White backgrounds
- Gray text hierarchy
- Blue accent colors
- Subtle shadows and borders

### Dark Mode
- Dark slate backgrounds (`--bg-primary: #0f172a`)
- Light text on dark (`--text-primary: #f8fafc`)  
- Adjusted contrast ratios
- Maintained accessibility standards

### Theme Toggle
```javascript
// Theme is managed by Alpine.js
function themeManager() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true',
        toggleTheme() {
            this.darkMode = !this.darkMode;
        }
    }
}
```

## Performance Optimizations

### CSS Loading Strategy
```blade
{{-- Critical CSS inline --}}
@if (file_exists(public_path('build/manifest.json')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    {{-- Fallback for non-Vite environments --}}
    <link rel="stylesheet" href="{{ asset('resources/css/app.css') }}">
@endif
```

### JavaScript Loading
- Alpine.js for reactive components
- Minimal JavaScript footprint
- Progressive enhancement approach
- Lazy loading for non-critical features

### Image Optimization
- WebP format support with fallbacks
- Responsive image loading
- Proper alt text for accessibility
- Icon fonts (Font Awesome) for scalability

## Accessibility Features

### WCAG Compliance
- Semantic HTML5 structure
- Proper heading hierarchy (h1, h2, h3...)
- Focus management and keyboard navigation
- Screen reader compatible
- High contrast support
- Reduced motion respect

### Skip Links
```html
<a href="#main-content" class="skip-link">Skip to main content</a>
```

### ARIA Labels
```html
<button aria-label="Toggle Menu" @click="toggleSidebar()">
<main role="main" id="main-content">
```

## Browser Support

### Modern Browsers (Full Support)
- Chrome 90+
- Firefox 88+  
- Safari 14+
- Edge 90+

### CSS Grid & Flexbox
- Full CSS Grid support required
- Flexbox fallbacks provided
- Progressive enhancement for older browsers

### JavaScript Requirements
- ES6+ features used
- Alpine.js requires modern JavaScript
- Graceful degradation for disabled JavaScript

## File Structure

```
resources/
├── views/
│   ├── layouts/
│   │   ├── master.blade.php           # Master layout
│   │   └── partials/
│   │       ├── header.blade.php       # Header component
│   │       ├── sidebar.blade.php      # Sidebar component  
│   │       ├── footer.blade.php       # Footer component
│   │       └── flash-messages.blade.php # Flash messages
│   └── example-dashboard.blade.php    # Usage example
├── css/
│   ├── app.css                        # Main CSS file
│   └── layout-system.css              # Layout system CSS
└── js/
    └── app.js                         # Main JavaScript file
```

## Customization Guide

### Adding New Roles
1. Update role detection in `master.blade.php`:
```blade
'new-role-layout': userRole === 'new-role'
```

2. Add CSS in `layout-system.css`:
```css
.new-role-layout {
    --sidebar-bg: linear-gradient(135deg, #color1, #color2);
    --header-accent: #color1;
}
```

3. Update sidebar navigation in `sidebar.blade.php`

### Creating Custom Sections
```blade
@section('custom-section')
    <!-- Your custom content -->
@endsection
```

Then in your layout:
```blade
@hasSection('custom-section')
    <div class="custom-section-container">
        @yield('custom-section')
    </div>
@endif
```

### Adding Breakpoints
```css
@media (min-width: 1600px) {
    .layout-grid {
        grid-template-columns: 320px 1fr;
    }
}
```

## Testing

### Cross-Device Testing
- Physical device testing on iOS/Android
- Browser developer tools simulation
- Responsive design mode verification
- Touch interface validation

### Accessibility Testing
- Screen reader testing (NVDA, JAWS, VoiceOver)
- Keyboard navigation verification  
- Color contrast validation
- Focus management testing

### Performance Testing
- Lighthouse audit scores
- WebPageTest.org metrics
- Core Web Vitals monitoring
- Bundle size optimization

## Migration Guide

### From Existing Layouts
1. Replace existing `@extends` with `@extends('layouts.master')`
2. Move page content to `@section('content')`
3. Update CSS classes to use new utility system
4. Test responsive behavior across devices

### Breaking Changes
- Old layout files need updating
- CSS class names may need adjustment
- JavaScript event handling updated for Alpine.js
- Theme variables renamed for consistency

## Support and Maintenance

### Regular Updates
- Monitor browser compatibility
- Update dependencies regularly
- Performance optimization reviews
- Accessibility audit compliance

### Common Issues
- **CSS Grid not working**: Check browser support
- **Sidebar not showing**: Verify user role detection
- **Mobile layout broken**: Check viewport meta tag
- **Dark mode not switching**: Verify localStorage access

---

**Version**: 2.1.0  
**Last Updated**: January 2025  
**Compatibility**: Laravel 10+, PHP 8.1+, Modern Browsers

For questions or issues, refer to the development team or create an issue in the project repository.

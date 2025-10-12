# HD Tickets Logo Component Documentation

## Overview

The `application-logo` component provides a consistent, accessible, and performance-optimized way to display the HD Tickets logo across the application.

## Features

✅ **Multiple Size Options**: small, default, large, xl  
✅ **Dark Mode Support**: Automatic adaptation to dark themes  
✅ **High Performance**: Optimized SVG with WebP fallback  
✅ **Accessibility**: Proper alt text and focus states  
✅ **Responsive**: Scales appropriately on all devices  
✅ **Print Friendly**: Optimized for print media  

## Usage

### Basic Usage

```blade
<x-application-logo />
```

### With Size Variants

```blade
<!-- Small logo (24px height) -->
<x-application-logo size="small" />

<!-- Default logo (32px height) -->
<x-application-logo size="default" />

<!-- Large logo (48px height) -->
<x-application-logo size="large" />

<!-- Extra large logo (64px height) -->
<x-application-logo size="xl" />
```

### With Custom Classes

```blade
<x-application-logo 
    size="large" 
    class="transition-transform duration-200 hover:scale-105" 
/>
```

### With Lazy Loading (for below-the-fold content)

```blade
<x-application-logo 
    size="default" 
    lazy="true" 
/>
```

## Navigation Examples

### Desktop Navigation

```blade
<div class="shrink-0 flex items-center">
    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
        <x-application-logo 
            size="default" 
            class="transition-transform duration-200 hover:scale-105" 
        />
        <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            HD Tickets
        </span>
    </a>
</div>
```

### Mobile Navigation

```blade
<div class="mobile-nav-brand">
    <a href="{{ route('dashboard') }}" class="mobile-brand-link">
        <x-application-logo size="small" />
        <span class="mobile-brand-text">HD Tickets</span>
    </a>
</div>
```

### Authentication Pages

```blade
<div class="text-center mb-8">
    <x-application-logo size="xl" class="mx-auto mb-4" />
    <h1 class="text-2xl font-bold">Welcome to HD Tickets</h1>
</div>
```

## File Structure

```
public/
├── images/
│   ├── logo-hdtickets-enhanced.svg    # Primary SVG logo
│   └── logo-hdtickets.svg             # Legacy SVG logo
└── assets/images/
    ├── hdTicketsLogo.webp             # WebP fallback
    ├── hdTicketsLogo.png              # PNG fallback
    ├── hdTicketsLogo.jpg              # JPEG fallback
    └── hdTicketsLogo.gif              # GIF fallback

resources/views/components/
└── application-logo.blade.php         # Logo component

resources/css/
├── critical.css                       # Logo styles
└── components-backup.css              # Mobile logo styles
```

## Logo Files

### Primary Logo: `logo-hdtickets-enhanced.svg`
- **Format**: SVG
- **Size**: Vector (scalable)
- **Features**: Dark mode support, filters, accessibility
- **Use**: All modern browsers

### Fallback Logo: `hdTicketsLogo.webp`
- **Format**: WebP
- **Size**: 128x128px
- **Use**: Older browsers, noscript fallback

## Styling

### CSS Classes

- `.hd-logo-container`: Container wrapper
- `.hd-logo`: Logo image styles
- `.mobile-logo`: Legacy mobile-specific styles

### Size Classes

- `h-6 w-auto`: Small (24px)
- `h-8 w-auto`: Default (32px)  
- `h-12 w-auto`: Large (48px)
- `h-16 w-auto`: Extra Large (64px)

## Accessibility Features

- **Alt Text**: Descriptive alternative text
- **Focus States**: Visible focus indicators
- **High Contrast**: Enhanced contrast in high contrast mode
- **Reduced Motion**: Respects user motion preferences
- **Screen Reader**: Proper ARIA labels and roles

## Performance Optimizations

- **Lazy Loading**: Optional lazy loading for non-critical logos
- **Async Decoding**: Non-blocking image decoding
- **Optimized SVG**: Minimal file size with modern features
- **WebP Fallback**: Smaller file size for supported browsers
- **CSS Optimizations**: Hardware acceleration, backface visibility

## Browser Support

- **Modern Browsers**: Full SVG support with dark mode
- **Legacy Browsers**: Automatic WebP/PNG fallback
- **No JavaScript**: Works with noscript fallback

## Testing

A comprehensive test page is available at `/logo-test.html` for:
- Size variant testing
- Dark mode compatibility
- Navigation simulations
- Accessibility validation
- Performance monitoring

## Migration from Old Logo

### Before (Old Implementation)
```blade
<img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
     alt="HD Tickets Logo" 
     class="h-8 w-auto">
```

### After (New Implementation)
```blade
<x-application-logo size="default" />
```

## Best Practices

1. **Use SVG First**: The enhanced SVG provides the best quality and performance
2. **Choose Appropriate Size**: Match logo size to context and hierarchy
3. **Include Alt Text**: Always provided automatically by the component
4. **Consider Context**: Use appropriate size for mobile vs desktop
5. **Test Dark Mode**: Verify logo visibility in both light and dark themes
6. **Performance**: Use lazy loading for logos below the fold

## Troubleshooting

### Logo Not Displaying
- Check file permissions on logo files
- Verify asset compilation with `npm run build`
- Check browser console for loading errors

### Dark Mode Issues
- Ensure the enhanced SVG is being used
- Check CSS media queries for dark mode
- Verify browser supports `prefers-color-scheme`

### Performance Issues
- Use lazy loading for non-critical logos
- Optimize logo file sizes if needed
- Check for unnecessary CSS filters

## Updates and Maintenance

- Logo files are cached by browsers - clear cache after updates
- Update version numbers when changing logo files
- Test across all supported browsers after changes
- Maintain fallback images for backwards compatibility
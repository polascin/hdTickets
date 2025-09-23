export default {
  plugins: {
    // Tailwind removed: only autoprefixer + (prod) cssnano remain
    // Autoprefixer - automatically add vendor prefixes based on browser support
    autoprefixer: {
      // Target browsers based on browserslist config
      overrideBrowserslist: [
        '> 1%',
        'last 2 versions',
        'not dead',
        'not ie <= 11'
      ],
      // Grid support
      grid: 'autoplace',
      // Remove outdated prefixes
      remove: true,
      // Add prefixes for new CSS features
      add: true,
      // Support for flexbox bugs
      flexbox: 'no-2009'
    },
    
    // CSSnano - CSS minification and optimization (production only)
    ...(process.env.NODE_ENV === 'production' ? {
      cssnano: {
        preset: [
          'default',
          {
            // Disable unsafe optimizations
            discardComments: {
              removeAll: true,
            },
            // Normalize whitespace
            normalizeWhitespace: true,
            // Merge rules where possible
            mergeRules: true,
            // Optimize font weights
            minifyFontValues: true,
            // Optimize selectors
            minifySelectors: true,
            // Remove unused at-rules
            discardUnused: false, // Keep false to avoid removing used CSS
            // Z-index optimization
            zindex: false, // Keep false to avoid z-index conflicts
            // Color optimization
            colormin: true,
            // Convert values to shorter forms
            convertValues: true,
            // Merge media queries
            mergeIdents: false, // Keep false to avoid keyframe conflicts
            // Reduce transform functions
            reduceTransforms: true,
            // Reduce initial values
            reduceInitial: true,
            // Normalize display values
            normalizeDisplayValues: true,
            // Normalize positions
            normalizePositions: true,
            // Normalize repeat style
            normalizeRepeatStyle: true,
            // Normalize string quotes
            normalizeString: true,
            // Normalize timing functions
            normalizeTimingFunctions: true,
            // Normalize unicode
            normalizeUnicode: true,
            // Normalize URL
            normalizeUrl: true,
            // Optimize calc() expressions
            calc: {
              precision: 3
            }
          }
        ]
      }
    } : {})
  }
};

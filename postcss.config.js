export default {
    plugins: {
        '@tailwindcss/postcss': {},
        autoprefixer: {
            // Enhanced browser support for modern applications
            overrideBrowserslist: [
                'last 2 versions',
                '> 1%',
                'not dead',
                'not ie 11',
                'not op_mini all',
                'chrome >= 87',
                'firefox >= 78',
                'safari >= 14',
                'edge >= 88'
            ],
            // Enhanced autoprefixer options
            grid: true,
            flexbox: 'no-2009',
            supports: true,
            cascade: false // Better for development debugging
        },
        // Enhanced CSS Nano configuration for production optimization
        ...(process.env.NODE_ENV === 'production' ? {
            cssnano: {
                preset: [
                    'default',
                    {
                        discardComments: {
                            removeAll: true,
                        },
                        normalizeWhitespace: true,
                        // Enhanced optimizations
                        reduceIdents: false, // Preserve animation names
                        zindex: false, // Preserve z-index values
                        discardUnused: false, // Keep all CSS for dynamic content
                        mergeIdents: false,
                        // Better CSS optimization
                        cssDeclarationSorter: {
                            order: 'alphabetically'
                        },
                        calc: {
                            precision: 2
                        }
                    },
                ],
            }
        } : {
            // Development-only plugins for better debugging
            'postcss-reporter': {
                clearReportedMessages: true
            }
        }),
    },
};

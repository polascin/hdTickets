export default {
    plugins: {
        tailwindcss: {},
        autoprefixer: {
            // Support the last 2 versions of all browsers
            overrideBrowserslist: [
                'last 2 versions',
                '> 1%',
                'not dead',
                'not ie 11'
            ],
            // Enable grid support
            grid: true,
            // Add vendor prefixes to flexbox
            flexbox: 'no-2009'
        },
        // Add CSS Nano for production optimization
        ...(process.env.NODE_ENV === 'production' ? {
            cssnano: {
                preset: [
                    'default',
                    {
                        discardComments: {
                            removeAll: true,
                        },
                        normalizeWhitespace: true,
                    },
                ],
            }
        } : {}),
    },
};

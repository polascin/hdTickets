/**
 * CSS Timestamp Utility for Cache Busting
 * Provides client-side CSS timestamp functionality for cache prevention
 */

class CSSTimestampUtility {
    constructor() {
        this.cache = new Map();
        this.basePath = window.location.origin;
        this.timestampParam = 'v';
        this.development = process.env.NODE_ENV === 'development' || window.location.hostname === 'localhost';
    }

    /**
     * Generate timestamped CSS URL
     * @param {string} cssPath - Path to CSS file
     * @param {object} options - Configuration options
     * @returns {string} Timestamped CSS URL
     */
    generateTimestampedUrl(cssPath, options = {}) {
        const {
            forceRefresh = false,
            useFileModTime = true,
            customTimestamp = null,
            development = this.development
        } = options;

        // Return cached URL if available and not forcing refresh
        if (!forceRefresh && this.cache.has(cssPath)) {
            return this.cache.get(cssPath);
        }

        let timestamp;
        
        if (customTimestamp) {
            timestamp = customTimestamp;
        } else if (development || !useFileModTime) {
            // Use current timestamp for development or when file mod time is not available
            timestamp = Date.now();
        } else {
            // Try to get file modification time via HEAD request
            timestamp = this.getFileModificationTime(cssPath) || Date.now();
        }

        const url = this.buildTimestampedUrl(cssPath, timestamp);
        
        // Cache the result
        this.cache.set(cssPath, url);
        
        return url;
    }

    /**
     * Build timestamped URL
     * @param {string} cssPath - CSS file path
     * @param {number} timestamp - Timestamp to append
     * @returns {string} Complete timestamped URL
     */
    buildTimestampedUrl(cssPath, timestamp) {
        // Handle different path formats
        let fullUrl;
        
        if (cssPath.startsWith('http://') || cssPath.startsWith('https://')) {
            // Already a full URL
            fullUrl = cssPath;
        } else if (cssPath.startsWith('/')) {
            // Absolute path
            fullUrl = this.basePath + cssPath;
        } else {
            // Relative path - assume it's in the assets directory
            fullUrl = this.basePath + '/assets/css/' + cssPath;
        }

        // Add timestamp parameter
        const separator = fullUrl.includes('?') ? '&' : '?';
        return `${fullUrl}${separator}${this.timestampParam}=${timestamp}`;
    }

    /**
     * Dynamically load CSS with timestamp
     * @param {string} cssPath - Path to CSS file
     * @param {object} options - Configuration options
     * @returns {Promise} Promise that resolves when CSS is loaded
     */
    loadCSS(cssPath, options = {}) {
        return new Promise((resolve, reject) => {
            const {
                id = null,
                media = 'all',
                replaceExisting = true,
                insertBefore = null
            } = options;

            const timestampedUrl = this.generateTimestampedUrl(cssPath, options);
            
            // Check if link already exists
            const existingLink = id ? document.getElementById(id) : 
                document.querySelector(`link[href^="${cssPath.split('?')[0]}"]`);

            if (existingLink && replaceExisting) {
                existingLink.remove();
            }

            // Create new link element
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = timestampedUrl;
            link.media = media;
            
            if (id) {
                link.id = id;
            }

            // Add loading event listeners
            link.onload = () => {
                console.log(`CSS loaded successfully: ${timestampedUrl}`);
                resolve(link);
            };

            link.onerror = () => {
                console.error(`Failed to load CSS: ${timestampedUrl}`);
                reject(new Error(`Failed to load CSS: ${timestampedUrl}`));
            };

            // Insert into document
            if (insertBefore && insertBefore.parentNode) {
                insertBefore.parentNode.insertBefore(link, insertBefore);
            } else {
                document.head.appendChild(link);
            }
        });
    }

    /**
     * Update existing CSS links with new timestamps
     * @param {string|Array} selectors - CSS selectors for links to update
     * @param {object} options - Configuration options
     */
    updateExistingCSS(selectors = 'link[rel="stylesheet"]', options = {}) {
        const links = typeof selectors === 'string' 
            ? document.querySelectorAll(selectors)
            : Array.isArray(selectors) 
                ? selectors.map(sel => document.querySelectorAll(sel)).flat()
                : [selectors];

        links.forEach(link => {
            if (link && link.href) {
                const originalPath = this.extractOriginalPath(link.href);
                const newUrl = this.generateTimestampedUrl(originalPath, { 
                    ...options, 
                    forceRefresh: true 
                });
                
                // Create a new link element to replace the old one
                const newLink = link.cloneNode(true);
                newLink.href = newUrl;
                
                newLink.onload = () => {
                    link.remove();
                    console.log(`CSS updated: ${newUrl}`);
                };

                newLink.onerror = () => {
                    console.error(`Failed to update CSS: ${newUrl}`);
                };

                // Insert new link after the old one
                link.parentNode.insertBefore(newLink, link.nextSibling);
            }
        });
    }

    /**
     * Extract original path from timestamped URL
     * @param {string} url - Timestamped URL
     * @returns {string} Original path without timestamp
     */
    extractOriginalPath(url) {
        const urlObj = new URL(url);
        urlObj.searchParams.delete(this.timestampParam);
        return urlObj.pathname;
    }

    /**
     * Get file modification time via HEAD request
     * @param {string} cssPath - Path to CSS file  
     * @returns {number|null} File modification timestamp or null
     */
    getFileModificationTime(cssPath) {
        try {
            // This would typically be done server-side, but we can try a HEAD request
            // Note: This may not work due to CORS restrictions
            const xhr = new XMLHttpRequest();
            xhr.open('HEAD', cssPath, false); // Synchronous for simplicity
            xhr.send();
            
            if (xhr.status === 200) {
                const lastModified = xhr.getResponseHeader('Last-Modified');
                return lastModified ? new Date(lastModified).getTime() : null;
            }
        } catch (error) {
            console.warn('Could not get file modification time:', error);
        }
        
        return null;
    }

    /**
     * Clear cache for specific CSS files or all
     * @param {string|Array} cssFiles - Specific files to clear or null for all
     */
    clearCache(cssFiles = null) {
        if (cssFiles === null) {
            this.cache.clear();
            console.log('CSS timestamp cache cleared');
        } else {
            const files = Array.isArray(cssFiles) ? cssFiles : [cssFiles];
            files.forEach(file => {
                this.cache.delete(file);
                console.log(`CSS timestamp cache cleared for: ${file}`);
            });
        }
    }

    /**
     * Get current cache size and contents
     * @returns {object} Cache information
     */
    getCacheInfo() {
        return {
            size: this.cache.size,
            entries: Array.from(this.cache.entries())
        };
    }

    /**
     * Watch for CSS file changes (development mode)
     * @param {string|Array} cssFiles - Files to watch
     * @param {function} callback - Callback when files change
     */
    watchCSS(cssFiles, callback = null) {
        if (!this.development) {
            console.warn('CSS watching is only available in development mode');
            return;
        }

        const files = Array.isArray(cssFiles) ? cssFiles : [cssFiles];
        const checkInterval = 2000; // Check every 2 seconds

        files.forEach(file => {
            let lastTimestamp = this.cache.get(file);
            
            setInterval(() => {
                const currentTimestamp = this.getFileModificationTime(file);
                
                if (currentTimestamp && currentTimestamp !== lastTimestamp) {
                    console.log(`CSS file changed: ${file}`);
                    
                    // Update cache
                    this.cache.set(file, currentTimestamp);
                    lastTimestamp = currentTimestamp;
                    
                    // Update CSS in document
                    this.updateExistingCSS(`link[href*="${file}"]`, { forceRefresh: true });
                    
                    // Call callback if provided
                    if (callback) {
                        callback(file, currentTimestamp);
                    }
                }
            }, checkInterval);
        });
    }

    /**
     * Create CSS link element with timestamp
     * @param {string} cssPath - Path to CSS file
     * @param {object} options - Configuration options
     * @returns {HTMLLinkElement} Link element
     */
    createCSSLink(cssPath, options = {}) {
        const {
            id = null,
            media = 'all',
            crossorigin = null,
            integrity = null
        } = options;

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = this.generateTimestampedUrl(cssPath, options);
        link.media = media;

        if (id) link.id = id;
        if (crossorigin) link.crossOrigin = crossorigin;
        if (integrity) link.integrity = integrity;

        return link;
    }
}

// Create and export singleton instance
const cssTimestamp = new CSSTimestampUtility();

// Make it globally available for compatibility with server-side rendered content
window.cssTimestamp = cssTimestamp;

// Helper functions for global access
window.timestampCSS = (cssPath, options = {}) => {
    return cssTimestamp.generateTimestampedUrl(cssPath, options);
};

window.loadCSSWithTimestamp = (cssPath, options = {}) => {
    return cssTimestamp.loadCSS(cssPath, options);
};

window.updateAllCSS = (options = {}) => {
    return cssTimestamp.updateExistingCSS('link[rel="stylesheet"]', options);
};

export default cssTimestamp;

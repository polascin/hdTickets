/**
 * HD Tickets Debouncing and Performance Utilities
 * 
 * Collection of utilities for optimizing performance in user interactions,
 * API calls, and frequent operations.
 * 
 * @version 1.0.0
 * @author HD Tickets Development Team
 */

class PerformanceUtils {
    constructor() {
        this.debouncedFunctions = new Map();
        this.throttledFunctions = new Map();
        this.rafCallbacks = new Map();
        this.observerInstances = new Map();
    }
    
    /**
     * Create a debounced function
     */
    debounce(func, wait, options = {}) {
        const { immediate = false, maxWait = null } = options;
        
        let timeoutId;
        let maxTimeoutId;
        let lastCallTime;
        let lastInvokeTime = 0;
        let lastArgs;
        let lastThis;
        let result;
        
        function shouldInvoke(time) {
            const timeSinceLastCall = time - lastCallTime;
            const timeSinceLastInvoke = time - lastInvokeTime;
            
            return (lastCallTime === undefined || 
                    timeSinceLastCall >= wait ||
                    timeSinceLastCall < 0 ||
                    (maxWait !== null && timeSinceLastInvoke >= maxWait));
        }
        
        function invokeFunc(time) {
            const args = lastArgs;
            const thisArg = lastThis;
            
            lastArgs = undefined;
            lastThis = undefined;
            lastInvokeTime = time;
            result = func.apply(thisArg, args);
            return result;
        }
        
        function leadingEdge(time) {
            lastInvokeTime = time;
            timeoutId = setTimeout(timerExpired, wait);
            return immediate ? invokeFunc(time) : result;
        }
        
        function timerExpired() {
            const time = Date.now();
            if (shouldInvoke(time)) {
                return trailingEdge(time);
            }
            timeoutId = setTimeout(timerExpired, wait - (time - lastCallTime));
        }
        
        function trailingEdge(time) {
            timeoutId = undefined;
            
            if (lastArgs) {
                return invokeFunc(time);
            }
            lastArgs = undefined;
            lastThis = undefined;
            return result;
        }
        
        function cancel() {
            if (timeoutId !== undefined) {
                clearTimeout(timeoutId);
            }
            if (maxTimeoutId !== undefined) {
                clearTimeout(maxTimeoutId);
            }
            lastInvokeTime = 0;
            lastArgs = undefined;
            lastCallTime = undefined;
            lastThis = undefined;
            timeoutId = undefined;
            maxTimeoutId = undefined;
        }
        
        function flush() {
            return timeoutId === undefined ? result : trailingEdge(Date.now());
        }
        
        function debounced(...args) {
            const time = Date.now();
            const isInvoking = shouldInvoke(time);
            
            lastArgs = args;
            lastThis = this;
            lastCallTime = time;
            
            if (isInvoking) {
                if (timeoutId === undefined) {
                    return leadingEdge(lastCallTime);
                }
                if (maxWait !== null) {
                    timeoutId = setTimeout(timerExpired, wait);
                    return invokeFunc(lastCallTime);
                }
            }
            if (timeoutId === undefined) {
                timeoutId = setTimeout(timerExpired, wait);
            }
            return result;
        }
        
        debounced.cancel = cancel;
        debounced.flush = flush;
        
        return debounced;
    }
    
    /**
     * Create a throttled function
     */
    throttle(func, wait, options = {}) {
        const { leading = true, trailing = true } = options;
        return this.debounce(func, wait, {
            maxWait: wait,
            immediate: leading
        });
    }
    
    /**
     * Request animation frame with automatic cleanup
     */
    requestAnimationFrame(callback, key = null) {
        if (key && this.rafCallbacks.has(key)) {
            cancelAnimationFrame(this.rafCallbacks.get(key));
        }
        
        const rafId = requestAnimationFrame((...args) => {
            if (key) {
                this.rafCallbacks.delete(key);
            }
            callback(...args);
        });
        
        if (key) {
            this.rafCallbacks.set(key, rafId);
        }
        
        return rafId;
    }
    
    /**
     * Cancel animation frame by key
     */
    cancelAnimationFrame(key) {
        if (this.rafCallbacks.has(key)) {
            cancelAnimationFrame(this.rafCallbacks.get(key));
            this.rafCallbacks.delete(key);
            return true;
        }
        return false;
    }
    
    /**
     * Create optimized search handler
     */
    createSearchHandler(searchFunction, options = {}) {
        const {
            debounceMs = 300,
            minLength = 2,
            maxLength = 100,
            cache = true,
            cacheSize = 50
        } = options;
        
        const searchCache = cache ? new Map() : null;
        const pendingRequests = new Map();
        
        const debouncedSearch = this.debounce(async (query, callback) => {
            // Validation
            if (query.length < minLength) {
                callback({ results: [], query, fromCache: false });
                return;
            }
            
            if (query.length > maxLength) {
                query = query.substring(0, maxLength);
            }
            
            // Check cache
            if (searchCache && searchCache.has(query)) {
                callback({ 
                    results: searchCache.get(query), 
                    query, 
                    fromCache: true 
                });
                return;
            }
            
            // Cancel previous request if still pending
            if (pendingRequests.has(query)) {
                const controller = pendingRequests.get(query);
                controller.abort();
            }
            
            // Create new request
            const controller = new AbortController();
            pendingRequests.set(query, controller);
            
            try {
                const results = await searchFunction(query, controller.signal);
                
                // Cache results
                if (searchCache) {
                    if (searchCache.size >= cacheSize) {
                        const firstKey = searchCache.keys().next().value;
                        searchCache.delete(firstKey);
                    }
                    searchCache.set(query, results);
                }
                
                callback({ results, query, fromCache: false });
            } catch (error) {
                if (error.name !== 'AbortError') {
                    callback({ results: [], query, error, fromCache: false });
                }
            } finally {
                pendingRequests.delete(query);
            }
        }, debounceMs);
        
        return {
            search: (query, callback) => debouncedSearch(query, callback),
            clearCache: () => searchCache && searchCache.clear(),
            getCacheSize: () => searchCache ? searchCache.size : 0,
            destroy: () => {
                debouncedSearch.cancel();
                pendingRequests.forEach(controller => controller.abort());
                pendingRequests.clear();
                if (searchCache) {
                    searchCache.clear();
                }
            }
        };
    }
    
    /**
     * Create optimized form handler
     */
    createFormHandler(formElement, options = {}) {
        const {
            debounceMs = 500,
            validateOnType = true,
            saveOnType = false,
            autosaveKey = null
        } = options;
        
        let isDirty = false;
        let lastSavedData = null;
        
        const getFormData = () => {
            const formData = new FormData(formElement);
            return Object.fromEntries(formData.entries());
        };
        
        const validateForm = async () => {
            if (options.validator) {
                const data = getFormData();
                return await options.validator(data);
            }
            return { isValid: true, errors: {} };
        };
        
        const saveForm = async () => {
            if (options.onSave) {
                const data = getFormData();
                await options.onSave(data);
                lastSavedData = JSON.stringify(data);
                isDirty = false;
                
                if (autosaveKey) {
                    localStorage.setItem(autosaveKey, lastSavedData);
                }
            }
        };
        
        const restoreForm = () => {
            if (autosaveKey) {
                const saved = localStorage.getItem(autosaveKey);
                if (saved) {
                    const data = JSON.parse(saved);
                    Object.entries(data).forEach(([name, value]) => {
                        const field = formElement.querySelector(`[name="${name}"]`);
                        if (field) {
                            field.value = value;
                        }
                    });
                }
            }
        };
        
        const debouncedValidation = validateOnType 
            ? this.debounce(validateForm, debounceMs)
            : null;
            
        const debouncedSave = saveOnType 
            ? this.debounce(saveForm, debounceMs * 2)
            : null;
        
        const handleInput = () => {
            isDirty = true;
            
            if (debouncedValidation) {
                debouncedValidation();
            }
            
            if (debouncedSave) {
                debouncedSave();
            }
            
            if (options.onInput) {
                options.onInput();
            }
        };
        
        // Add event listeners
        formElement.addEventListener('input', handleInput);
        formElement.addEventListener('change', handleInput);
        
        return {
            isDirty: () => isDirty,
            validate: validateForm,
            save: saveForm,
            restore: restoreForm,
            getFormData,
            destroy: () => {
                formElement.removeEventListener('input', handleInput);
                formElement.removeEventListener('change', handleInput);
                if (debouncedValidation) debouncedValidation.cancel();
                if (debouncedSave) debouncedSave.cancel();
            }
        };
    }
    
    /**
     * Create intersection observer with performance optimizations
     */
    createIntersectionObserver(callback, options = {}) {
        const {
            rootMargin = '0px',
            threshold = 0.1,
            debounceMs = 100
        } = options;
        
        const debouncedCallback = this.debounce(callback, debounceMs);
        
        const observer = new IntersectionObserver(debouncedCallback, {
            rootMargin,
            threshold
        });
        
        return {
            observe: (element) => observer.observe(element),
            unobserve: (element) => observer.unobserve(element),
            disconnect: () => observer.disconnect(),
            destroy: () => {
                debouncedCallback.cancel();
                observer.disconnect();
            }
        };
    }
    
    /**
     * Create resize observer with debouncing
     */
    createResizeObserver(callback, debounceMs = 100) {
        const debouncedCallback = this.debounce(callback, debounceMs);
        
        const observer = new ResizeObserver(debouncedCallback);
        
        return {
            observe: (element) => observer.observe(element),
            unobserve: (element) => observer.unobserve(element),
            disconnect: () => observer.disconnect(),
            destroy: () => {
                debouncedCallback.cancel();
                observer.disconnect();
            }
        };
    }
    
    /**
     * Create optimized scroll handler
     */
    createScrollHandler(callback, options = {}) {
        const {
            throttleMs = 16, // ~60fps
            useRAF = true,
            passive = true
        } = options;
        
        let isScrolling = false;
        let rafId = null;
        
        const handleScroll = useRAF 
            ? (event) => {
                if (rafId) {
                    cancelAnimationFrame(rafId);
                }
                
                rafId = requestAnimationFrame(() => {
                    callback(event);
                    isScrolling = false;
                });
                
                if (!isScrolling) {
                    isScrolling = true;
                    if (options.onScrollStart) {
                        options.onScrollStart(event);
                    }
                }
            }
            : this.throttle(callback, throttleMs);
        
        return {
            attach: (element) => {
                element.addEventListener('scroll', handleScroll, { passive });
            },
            detach: (element) => {
                element.removeEventListener('scroll', handleScroll);
                if (rafId) {
                    cancelAnimationFrame(rafId);
                }
            },
            isScrolling: () => isScrolling,
            destroy: () => {
                if (rafId) {
                    cancelAnimationFrame(rafId);
                }
                if (handleScroll.cancel) {
                    handleScroll.cancel();
                }
            }
        };
    }
    
    /**
     * Batch DOM operations for better performance
     */
    batchDOMOperations(operations) {
        return new Promise((resolve) => {
            requestAnimationFrame(() => {
                const results = operations.map(op => {
                    if (typeof op === 'function') {
                        return op();
                    }
                    return op;
                });
                resolve(results);
            });
        });
    }
    
    /**
     * Measure performance of operations
     */
    measurePerformance(name, operation) {
        return new Promise(async (resolve, reject) => {
            const startTime = performance.now();
            
            try {
                const result = await operation();
                const endTime = performance.now();
                const duration = endTime - startTime;
                
                console.log(`[Performance] ${name}: ${duration.toFixed(2)}ms`);
                
                resolve({ result, duration });
            } catch (error) {
                const endTime = performance.now();
                const duration = endTime - startTime;
                
                console.error(`[Performance] ${name} failed after ${duration.toFixed(2)}ms:`, error);
                reject(error);
            }
        });
    }
    
    /**
     * Clean up all resources
     */
    destroy() {
        this.debouncedFunctions.clear();
        this.throttledFunctions.clear();
        
        this.rafCallbacks.forEach(rafId => cancelAnimationFrame(rafId));
        this.rafCallbacks.clear();
        
        this.observerInstances.forEach(observer => {
            if (observer.disconnect) observer.disconnect();
            if (observer.destroy) observer.destroy();
        });
        this.observerInstances.clear();
    }
}

// Search input enhancement
class SearchInput {
    constructor(inputElement, options = {}) {
        this.input = typeof inputElement === 'string' 
            ? document.querySelector(inputElement)
            : inputElement;
            
        if (!this.input) {
            throw new Error('Search input element not found');
        }
        
        this.options = {
            debounceMs: 300,
            minLength: 2,
            maxResults: 10,
            showLoader: true,
            showNoResults: true,
            resultsContainer: null,
            loaderTemplate: '<div class="search-loader">Searching...</div>',
            noResultsTemplate: '<div class="search-no-results">No results found</div>',
            resultTemplate: null,
            onSearch: null,
            onSelect: null,
            onClear: null,
            ...options
        };
        
        this.performanceUtils = new PerformanceUtils();
        this.isSearching = false;
        this.currentQuery = '';
        this.results = [];
        
        this.init();
    }
    
    init() {
        this.createResultsContainer();
        this.setupSearchHandler();
        this.setupEventListeners();
        this.setupKeyboardNavigation();
    }
    
    createResultsContainer() {
        if (!this.options.resultsContainer) {
            this.resultsContainer = document.createElement('div');
            this.resultsContainer.className = 'search-results';
            this.resultsContainer.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #e5e7eb;
                border-top: none;
                max-height: 300px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
            `;
            
            // Position relative to input
            this.input.style.position = 'relative';
            this.input.parentNode.appendChild(this.resultsContainer);
        } else {
            this.resultsContainer = this.options.resultsContainer;
        }
    }
    
    setupSearchHandler() {
        this.searchHandler = this.performanceUtils.createSearchHandler(
            async (query, signal) => {
                if (this.options.onSearch) {
                    return await this.options.onSearch(query, signal);
                }
                return [];
            },
            {
                debounceMs: this.options.debounceMs,
                minLength: this.options.minLength
            }
        );
    }
    
    setupEventListeners() {
        this.input.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            this.handleSearch(query);
        });
        
        this.input.addEventListener('focus', () => {
            if (this.results.length > 0) {
                this.showResults();
            }
        });
        
        this.input.addEventListener('blur', (e) => {
            // Delay hiding to allow clicking on results
            setTimeout(() => {
                if (!this.resultsContainer.contains(document.activeElement)) {
                    this.hideResults();
                }
            }, 150);
        });
        
        // Handle clicking outside
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.resultsContainer.contains(e.target)) {
                this.hideResults();
            }
        });
    }
    
    setupKeyboardNavigation() {
        let selectedIndex = -1;
        
        this.input.addEventListener('keydown', (e) => {
            const resultItems = this.resultsContainer.querySelectorAll('.search-result-item');
            
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, resultItems.length - 1);
                    this.updateSelection(resultItems, selectedIndex);
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    this.updateSelection(resultItems, selectedIndex);
                    break;
                    
                case 'Enter':
                    e.preventDefault();
                    if (selectedIndex >= 0 && resultItems[selectedIndex]) {
                        this.selectResult(this.results[selectedIndex], selectedIndex);
                    }
                    break;
                    
                case 'Escape':
                    this.hideResults();
                    this.input.blur();
                    selectedIndex = -1;
                    break;
            }
        });
    }
    
    handleSearch(query) {
        this.currentQuery = query;
        
        if (query.length < this.options.minLength) {
            this.hideResults();
            return;
        }
        
        this.showLoader();
        this.isSearching = true;
        
        this.searchHandler.search(query, (result) => {
            // Check if this is still the current query
            if (this.currentQuery !== query) {
                return;
            }
            
            this.isSearching = false;
            
            if (result.error) {
                console.error('Search error:', result.error);
                this.showNoResults();
                return;
            }
            
            this.results = result.results.slice(0, this.options.maxResults);
            
            if (this.results.length === 0) {
                this.showNoResults();
            } else {
                this.renderResults();
                this.showResults();
            }
        });
    }
    
    showLoader() {
        if (this.options.showLoader) {
            this.resultsContainer.innerHTML = this.options.loaderTemplate;
            this.showResults();
        }
    }
    
    showNoResults() {
        if (this.options.showNoResults) {
            this.resultsContainer.innerHTML = this.options.noResultsTemplate;
            this.showResults();
        }
    }
    
    renderResults() {
        const html = this.results.map((result, index) => {
            if (this.options.resultTemplate) {
                return this.options.resultTemplate(result, index);
            }
            
            return `
                <div class="search-result-item" data-index="${index}">
                    ${result.title || result.name || result.label || JSON.stringify(result)}
                </div>
            `;
        }).join('');
        
        this.resultsContainer.innerHTML = html;
        
        // Add click handlers
        this.resultsContainer.querySelectorAll('.search-result-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                this.selectResult(this.results[index], index);
            });
        });
    }
    
    updateSelection(items, selectedIndex) {
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === selectedIndex);
        });
    }
    
    selectResult(result, index) {
        if (this.options.onSelect) {
            this.options.onSelect(result, index);
        }
        
        this.hideResults();
        this.input.value = result.title || result.name || result.label || '';
    }
    
    showResults() {
        this.resultsContainer.style.display = 'block';
    }
    
    hideResults() {
        this.resultsContainer.style.display = 'none';
    }
    
    clear() {
        this.input.value = '';
        this.results = [];
        this.currentQuery = '';
        this.hideResults();
        
        if (this.options.onClear) {
            this.options.onClear();
        }
    }
    
    destroy() {
        this.searchHandler.destroy();
        this.performanceUtils.destroy();
        this.resultsContainer.remove();
    }
}

// Create global instance
window.performanceUtils = new PerformanceUtils();

// Expose utilities globally
window.debounce = (func, wait, options) => window.performanceUtils.debounce(func, wait, options);
window.throttle = (func, wait, options) => window.performanceUtils.throttle(func, wait, options);

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PerformanceUtils, SearchInput };
}

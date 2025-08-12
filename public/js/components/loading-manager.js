/**
 * Loading Manager Module
 * Handles loading states for AJAX operations and form submissions
 */

export class LoadingManager {
    static loadingElements = new Map();
    
    /**
     * Show loading state for an element
     */
    static showLoading(element, options = {}) {
        const config = {
            text: 'Loading...',
            spinner: true,
            overlay: true,
            disableElement: true,
            className: 'loading-state',
            ...options
        };
        
        if (this.loadingElements.has(element)) {
            return; // Already loading
        }
        
        // Store original state
        const originalState = {
            disabled: element.disabled,
            innerHTML: element.innerHTML,
            className: element.className
        };
        
        this.loadingElements.set(element, originalState);
        
        // Disable element if requested
        if (config.disableElement) {
            element.disabled = true;
        }
        
        // Add loading class
        element.classList.add(config.className);
        
        // Create loading content
        let loadingContent = '';
        if (config.spinner) {
            loadingContent += '<span class="loading-spinner"></span>';
        }
        if (config.text) {
            loadingContent += `<span class="loading-text">${config.text}</span>`;
        }
        
        // Update element content
        if (element.tagName === 'BUTTON' || element.tagName === 'INPUT') {
            element.innerHTML = loadingContent;
        }
        
        // Add overlay if requested
        if (config.overlay && element.tagName === 'FORM') {
            this.addOverlay(element);
        }
    }
    
    /**
     * Hide loading state for an element
     */
    static hideLoading(element) {
        if (!this.loadingElements.has(element)) {
            return; // Not loading
        }
        
        const originalState = this.loadingElements.get(element);
        
        // Restore original state
        element.disabled = originalState.disabled;
        element.innerHTML = originalState.innerHTML;
        element.className = originalState.className;
        
        // Remove overlay if present
        this.removeOverlay(element);
        
        // Clean up
        this.loadingElements.delete(element);
    }
    
    /**
     * Add overlay to element
     */
    static addOverlay(element) {
        const existingOverlay = element.querySelector('.loading-overlay');
        if (existingOverlay) {
            return;
        }
        
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-overlay-content">
                <div class="loading-spinner-large"></div>
                <div class="loading-message">Processing...</div>
            </div>
        `;
        
        element.style.position = 'relative';
        element.appendChild(overlay);
    }
    
    /**
     * Remove overlay from element
     */
    static removeOverlay(element) {
        const overlay = element.querySelector('.loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
    
    /**
     * Wrap AJAX operation with loading state
     */
    static async wrapAjax(element, promise, options = {}) {
        this.showLoading(element, options);
        
        try {
            const result = await promise;
            return result;
        } finally {
            this.hideLoading(element);
        }
    }
    
    /**
     * Show global loading indicator
     */
    static showGlobalLoading(message = 'Loading...') {
        this.hideGlobalLoading(); // Remove any existing loader
        
        const loader = document.createElement('div');
        loader.id = 'global-loading-indicator';
        loader.className = 'global-loading';
        loader.innerHTML = `
            <div class="global-loading-backdrop"></div>
            <div class="global-loading-content">
                <div class="global-loading-spinner"></div>
                <div class="global-loading-message">${message}</div>
            </div>
        `;
        
        document.body.appendChild(loader);
        
        // Prevent scrolling
        document.body.classList.add('loading-no-scroll');
    }
    
    /**
     * Hide global loading indicator
     */
    static hideGlobalLoading() {
        const loader = document.getElementById('global-loading-indicator');
        if (loader) {
            loader.remove();
        }
        
        // Re-enable scrolling
        document.body.classList.remove('loading-no-scroll');
    }
    
    /**
     * Show progress bar
     */
    static showProgress(percentage = 0, message = '') {
        let progressContainer = document.getElementById('progress-container');
        
        if (!progressContainer) {
            progressContainer = document.createElement('div');
            progressContainer.id = 'progress-container';
            progressContainer.className = 'progress-container';
            progressContainer.innerHTML = `
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-message"></div>
            `;
            document.body.appendChild(progressContainer);
        }
        
        const progressFill = progressContainer.querySelector('.progress-fill');
        const progressMessage = progressContainer.querySelector('.progress-message');
        
        progressFill.style.width = `${Math.min(Math.max(percentage, 0), 100)}%`;
        progressMessage.textContent = message;
        
        progressContainer.style.display = 'block';
    }
    
    /**
     * Hide progress bar
     */
    static hideProgress() {
        const progressContainer = document.getElementById('progress-container');
        if (progressContainer) {
            progressContainer.style.display = 'none';
        }
    }
    
    /**
     * Auto-hide progress after completion
     */
    static completeProgress(message = 'Complete!', delay = 2000) {
        this.showProgress(100, message);
        
        setTimeout(() => {
            this.hideProgress();
        }, delay);
    }
}

// Add default loading styles
if (!document.getElementById('loading-manager-styles')) {
    const style = document.createElement('style');
    style.id = 'loading-manager-styles';
    style.textContent = `
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        .loading-spinner-large {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-state {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .loading-overlay-content {
            text-align: center;
            padding: 20px;
        }
        
        .loading-message {
            margin-top: 12px;
            color: #666;
            font-weight: 500;
        }
        
        .global-loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .global-loading-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .global-loading-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }
        
        .global-loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        .global-loading-message {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }
        
        .loading-no-scroll {
            overflow: hidden;
        }
        
        .progress-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 9000;
            padding: 16px;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 4px;
            transition: width 0.3s ease;
            width: 0%;
        }
        
        .progress-message {
            margin-top: 8px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        
        .loading-text {
            margin-left: 4px;
        }
    `;
    document.head.appendChild(style);
}

// Export for global use
window.LoadingManager = LoadingManager;

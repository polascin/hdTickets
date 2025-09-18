/**
 * HD Tickets - Dropdown Positioning Fix
 * 
 * Ensures dropdown menus are properly positioned and visible
 * above all dashboard content.
 */

class DropdownPositionFixer {
    constructor() {
        this.activeDropdowns = new Set();
        this.init();
    }

    init() {
        // Wait for Alpine.js to initialize
        document.addEventListener('alpine:init', () => {
            this.setupDropdownObservers();
        });

        // Fallback if Alpine.js is not available
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => this.setupDropdownObservers(), 100);
            });
        } else {
            setTimeout(() => this.setupDropdownObservers(), 100);
        }
    }

    setupDropdownObservers() {
        // Find all dropdown elements
        const dropdowns = document.querySelectorAll('[data-dropdown]');
        
        dropdowns.forEach(dropdown => {
            this.observeDropdown(dropdown);
        });

        // Setup mutation observer for dynamically added dropdowns
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const newDropdowns = node.querySelectorAll('[data-dropdown]');
                        newDropdowns.forEach(dropdown => {
                            this.observeDropdown(dropdown);
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    observeDropdown(dropdown) {
        // Create intersection observer to detect visibility issues
        const intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.ensureProperZIndex(entry.target);
                }
            });
        });

        // Create mutation observer for dropdown state changes
        const mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes') {
                    const dropdown = mutation.target;
                    this.handleDropdownVisibilityChange(dropdown);
                }
            });
        });

        // Observe visibility changes
        mutationObserver.observe(dropdown, {
            attributes: true,
            attributeFilter: ['style', 'class', 'x-show']
        });

        intersectionObserver.observe(dropdown);
    }

    handleDropdownVisibilityChange(dropdown) {
        const isVisible = this.isDropdownVisible(dropdown);
        
        if (isVisible) {
            this.showDropdown(dropdown);
        } else {
            this.hideDropdown(dropdown);
        }
    }

    isDropdownVisible(dropdown) {
        const style = window.getComputedStyle(dropdown);
        const isDisplayed = style.display !== 'none';
        const isVisible = style.visibility !== 'hidden';
        const hasOpacity = parseFloat(style.opacity) > 0;
        
        return isDisplayed && isVisible && hasOpacity;
    }

    showDropdown(dropdown) {
        this.activeDropdowns.add(dropdown);
        
        // Apply high z-index
        this.ensureProperZIndex(dropdown);
        
        // Add state classes
        document.body.classList.add('dropdown-active');
        
        // Fix positioning if needed
        this.fixPositioning(dropdown);
        
        console.log('📍 Dropdown shown:', dropdown);
    }

    hideDropdown(dropdown) {
        this.activeDropdowns.delete(dropdown);
        
        if (this.activeDropdowns.size === 0) {
            document.body.classList.remove('dropdown-active');
        }
        
        console.log('📍 Dropdown hidden:', dropdown);
    }

    ensureProperZIndex(dropdown) {
        // Force very high z-index
        dropdown.style.zIndex = '99999';
        dropdown.style.position = 'absolute';
        
        // Ensure parent containers don't interfere
        let parent = dropdown.parentElement;
        while (parent && parent !== document.body) {
            const parentStyle = window.getComputedStyle(parent);
            
            // Reset problematic CSS properties
            if (parentStyle.transform !== 'none') {
                parent.style.transform = 'none';
            }
            
            if (parentStyle.isolation === 'isolate') {
                parent.style.isolation = 'auto';
            }
            
            parent = parent.parentElement;
        }
    }

    fixPositioning(dropdown) {
        // Get trigger button
        const trigger = this.findDropdownTrigger(dropdown);
        if (!trigger) return;

        const triggerRect = trigger.getBoundingClientRect();
        const dropdownRect = dropdown.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Calculate optimal position
        let left = triggerRect.left;
        let top = triggerRect.bottom + 5;

        // Adjust for right edge overflow
        if (left + dropdownRect.width > viewportWidth - 20) {
            left = viewportWidth - dropdownRect.width - 20;
        }

        // Adjust for left edge overflow
        if (left < 20) {
            left = 20;
        }

        // Adjust for bottom edge overflow
        if (top + dropdownRect.height > viewportHeight - 20) {
            top = triggerRect.top - dropdownRect.height - 5;
        }

        // Apply positioning
        dropdown.style.left = left + 'px';
        dropdown.style.top = top + 'px';
        dropdown.style.position = 'fixed';
    }

    findDropdownTrigger(dropdown) {
        // Look for common trigger patterns
        const parent = dropdown.parentElement;
        if (!parent) return null;

        // Find button with aria-haspopup
        const trigger = parent.querySelector('[aria-haspopup="true"]') ||
                       parent.querySelector('[aria-expanded]') ||
                       parent.querySelector('button') ||
                       parent.previousElementSibling;

        return trigger;
    }

    // Public method to force fix all visible dropdowns
    fixAllDropdowns() {
        const visibleDropdowns = document.querySelectorAll('[data-dropdown]');
        
        visibleDropdowns.forEach(dropdown => {
            if (this.isDropdownVisible(dropdown)) {
                this.ensureProperZIndex(dropdown);
                this.fixPositioning(dropdown);
            }
        });
    }

    // Public method to reset all dropdown positioning
    resetAllDropdowns() {
        this.activeDropdowns.clear();
        document.body.classList.remove('dropdown-active');
        
        const allDropdowns = document.querySelectorAll('[data-dropdown]');
        allDropdowns.forEach(dropdown => {
            dropdown.style.zIndex = '';
            dropdown.style.position = '';
            dropdown.style.left = '';
            dropdown.style.top = '';
        });
    }
}

// Initialize the dropdown position fixer
let dropdownFixer;

if (typeof window !== 'undefined') {
    dropdownFixer = new DropdownPositionFixer();
    
    // Expose globally for debugging
    window.HDTicketsDropdownFixer = dropdownFixer;
    
    // Fix dropdowns on window resize
    window.addEventListener('resize', () => {
        dropdownFixer.fixAllDropdowns();
    });
    
    // Fix dropdowns on scroll
    window.addEventListener('scroll', () => {
        dropdownFixer.fixAllDropdowns();
    });
}

// Add CSS class helper for debugging
document.addEventListener('DOMContentLoaded', () => {
    // Add debug styles for development
    if (window.location.hostname === 'hdtickets.local') {
        const style = document.createElement('style');
        style.textContent = `
            .dropdown-debug [data-dropdown] {
                border: 2px solid red !important;
                box-shadow: 0 0 10px rgba(255, 0, 0, 0.5) !important;
            }
        `;
        document.head.appendChild(style);
        
        // Add keyboard shortcut for debugging
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F12' && e.shiftKey) {
                document.body.classList.toggle('dropdown-debug');
                console.log('Dropdown debug mode toggled');
            }
        });
    }
});

console.log('📍 HD Tickets Dropdown Position Fixer loaded');

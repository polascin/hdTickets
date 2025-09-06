/**
 * Simple Dropdown Debug Helper
 * Helps identify z-index and positioning issues
 */

console.log('🔍 Dropdown Debug Helper Loaded');

// Function to analyze dropdown issues
function analyzeDropdowns() {
    console.log('📊 Analyzing Dropdowns...');
    
    const dropdowns = document.querySelectorAll('.nav-dropdown, [data-dropdown]');
    const navigation = document.querySelector('#main-navigation');
    const dashboard = document.querySelector('.customer-dashboard');
    
    console.log(`Found ${dropdowns.length} dropdowns`);
    
    dropdowns.forEach((dropdown, index) => {
        const computed = window.getComputedStyle(dropdown);
        const rect = dropdown.getBoundingClientRect();
        
        console.log(`Dropdown ${index + 1}:`, {
            element: dropdown,
            zIndex: computed.zIndex,
            position: computed.position,
            display: computed.display,
            visibility: computed.visibility,
            opacity: computed.opacity,
            transform: computed.transform,
            isolation: computed.isolation,
            backdropFilter: computed.backdropFilter,
            dimensions: {
                width: rect.width,
                height: rect.height,
                top: rect.top,
                left: rect.left
            }
        });
    });
    
    if (navigation) {
        const navComputed = window.getComputedStyle(navigation);
        console.log('Navigation:', {
            zIndex: navComputed.zIndex,
            position: navComputed.position,
            isolation: navComputed.isolation
        });
    }
    
    if (dashboard) {
        const dashboardComputed = window.getComputedStyle(dashboard);
        console.log('Dashboard:', {
            zIndex: dashboardComputed.zIndex,
            position: dashboardComputed.position,
            isolation: dashboardComputed.isolation,
            backdropFilter: dashboardComputed.backdropFilter
        });
    }
}

// Function to force fix dropdowns
function forceFixDropdowns() {
    console.log('🔧 Applying Force Fix...');
    
    // Get all potential dropdown elements
    const dropdowns = document.querySelectorAll('.nav-dropdown, [data-dropdown], div[x-show*="dropdown"], div[x-show*="Dropdown"]');
    
    dropdowns.forEach((dropdown, index) => {
        dropdown.style.zIndex = '2147483647';
        dropdown.style.position = 'absolute';
        dropdown.style.isolation = 'isolate';
        dropdown.style.background = 'white';
        dropdown.style.border = '1px solid #e5e7eb';
        dropdown.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
        
        console.log(`Fixed dropdown ${index + 1}`, dropdown);
    });
    
    // Remove problematic styles from dashboard elements
    const dashboardElements = document.querySelectorAll('.dashboard-header, .stat-card, .action-card, .dashboard-card');
    dashboardElements.forEach(el => {
        el.style.backdropFilter = 'none';
        el.style.webkitBackdropFilter = 'none';
        el.style.transform = 'none';
        el.style.isolation = 'auto';
    });
    
    console.log('✅ Force fix applied');
}

// Auto-run analysis on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        analyzeDropdowns();
        forceFixDropdowns();
    }, 1000);
});

// Add global functions for manual debugging
window.analyzeDropdowns = analyzeDropdowns;
window.forceFixDropdowns = forceFixDropdowns;

// Add click handler to detect dropdown opens
document.addEventListener('click', function(e) {
    const trigger = e.target.closest('[aria-haspopup="true"], [aria-expanded]');
    if (trigger) {
        console.log('🖱️ Dropdown trigger clicked:', trigger);
        setTimeout(() => {
            analyzeDropdowns();
            forceFixDropdowns();
        }, 100);
    }
});

// Add keyboard shortcuts for debugging
document.addEventListener('keydown', function(e) {
    // Ctrl+Shift+D to analyze dropdowns
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        analyzeDropdowns();
    }
    
    // Ctrl+Shift+F to force fix
    if (e.ctrlKey && e.shiftKey && e.key === 'F') {
        e.preventDefault();
        forceFixDropdowns();
    }
    
    // Ctrl+Shift+T to toggle debug mode
    if (e.ctrlKey && e.shiftKey && e.key === 'T') {
        e.preventDefault();
        document.body.classList.toggle('dropdown-debug');
        console.log('🐛 Debug mode toggled');
    }
});

console.log('🎮 Debug shortcuts:');
console.log('- Ctrl+Shift+D: Analyze dropdowns');  
console.log('- Ctrl+Shift+F: Force fix dropdowns');
console.log('- Ctrl+Shift+T: Toggle debug mode');
console.log('- analyzeDropdowns(): Manual analysis');
console.log('- forceFixDropdowns(): Manual fix');

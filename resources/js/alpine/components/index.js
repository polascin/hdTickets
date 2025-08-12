/**
 * Alpine.js Components Index
 * Exports all Alpine.js components for the application
 */

import formHandler from './formHandler.js';
import tableManager from './tableManager.js';
import searchFilter from './searchFilter.js';
import confirmDialog from './confirmDialog.js';
import tooltip from './tooltip.js';
import dropdown from './dropdown.js';
import tabs from './tabs.js';
import accordion from './accordion.js';
import eventFilter from './eventFilter.js';

export {
    formHandler,
    tableManager,
    searchFilter,
    confirmDialog,
    tooltip,
    dropdown,
    tabs,
    accordion,
    eventFilter
};

// Component registry for Alpine components
window.AlpineComponents = {
    formHandler,
    tableManager,
    searchFilter,
    confirmDialog,
    tooltip,
    dropdown,
    tabs,
    accordion,
    eventFilter
};

// Auto-register components with Laravel Component Registry
document.addEventListener('DOMContentLoaded', function() {
    if (window.ComponentRegistry) {
        Object.keys(window.AlpineComponents).forEach(componentName => {
            window.ComponentRegistry.register(componentName, 'alpine', {
                lazy: false,
                category: 'interactive',
                description: `Alpine.js ${componentName} component`
            });
        });
    }
});

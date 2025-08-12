export default function dropdown(options = {}) {
    return {
        open: false,
        loading: false,
        error: null,
        items: options.items || [],
        searchTerm: '',
        selectedIndex: -1,
        
        // Configuration options
        config: {
            closeOnEscape: true,
            closeOnClickOutside: true,
            closeOnItemClick: true,
            searchable: false,
            keyboard: true,
            maxHeight: '200px',
            placement: 'bottom-start',
            offset: 4,
            ...options
        },
        
        init() {
            this.setupEventListeners();
            this.setupKeyboardNavigation();
            
            // Watch for open state changes
            this.$watch('open', (value) => {
                if (value) {
                    this.onOpen();
                } else {
                    this.onClose();
                }
            });
        },
        
        setupEventListeners() {
            // Close dropdown when clicking outside
            if (this.config.closeOnClickOutside) {
                document.addEventListener('click', (e) => {
                    if (!this.$el.contains(e.target)) {
                        this.close();
                    }
                });
            }
            
            // Close dropdown on escape key
            if (this.config.closeOnEscape) {
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.open) {
                        this.close();
                        // Return focus to trigger button
                        const trigger = this.$el.querySelector('[data-dropdown-trigger]');
                        if (trigger) trigger.focus();
                    }
                });
            }
            
            // Prevent dropdown from closing when clicking inside menu
            const menu = this.$el.querySelector('[data-dropdown-menu]');
            if (menu) {
                menu.addEventListener('click', (e) => {
                    if (!this.config.closeOnItemClick) {
                        e.stopPropagation();
                    }
                });
            }
        },
        
        setupKeyboardNavigation() {
            if (!this.config.keyboard) return;
            
            this.$el.addEventListener('keydown', (e) => {
                if (!this.open) return;
                
                const items = this.getMenuItems();
                if (items.length === 0) return;
                
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                        this.scrollToItem(this.selectedIndex);
                        break;
                        
                    case 'ArrowUp':
                        e.preventDefault();
                        this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                        this.scrollToItem(this.selectedIndex);
                        break;
                        
                    case 'Enter':
                        e.preventDefault();
                        if (this.selectedIndex >= 0) {
                            this.selectItem(items[this.selectedIndex]);
                        }
                        break;
                        
                    case 'Home':
                        e.preventDefault();
                        this.selectedIndex = 0;
                        this.scrollToItem(0);
                        break;
                        
                    case 'End':
                        e.preventDefault();
                        this.selectedIndex = items.length - 1;
                        this.scrollToItem(this.selectedIndex);
                        break;
                }
            });
        },
        
        onOpen() {
            this.selectedIndex = -1;
            this.error = null;
            
            // Focus first item or search input
            this.$nextTick(() => {
                const searchInput = this.$el.querySelector('[data-dropdown-search]');
                if (searchInput && this.config.searchable) {
                    searchInput.focus();
                } else {
                    const firstItem = this.getMenuItems()[0];
                    if (firstItem) {
                        this.selectedIndex = 0;
                    }
                }
            });
            
            // Emit open event
            this.$dispatch('dropdown-opened', { dropdown: this });
        },
        
        onClose() {
            this.selectedIndex = -1;
            this.searchTerm = '';
            
            // Emit close event
            this.$dispatch('dropdown-closed', { dropdown: this });
        },
        
        toggle() {
            this.open = !this.open;
            
            // Emit toggle event
            this.$dispatch('dropdown-toggled', { 
                open: this.open, 
                dropdown: this 
            });
        },
        
        open() {
            this.open = true;
        },
        
        close() {
            this.open = false;
        },
        
        selectItem(item, index = null) {
            if (item.disabled) return;
            
            this.$dispatch('dropdown-item-selected', { 
                item, 
                index: index !== null ? index : this.selectedIndex, 
                dropdown: this 
            });
            
            if (this.config.closeOnItemClick) {
                this.close();
            }
        },
        
        getMenuItems() {
            return Array.from(this.$el.querySelectorAll('[data-dropdown-item]:not([disabled])'));
        },
        
        scrollToItem(index) {
            const items = this.getMenuItems();
            if (items[index]) {
                items[index].scrollIntoView({ 
                    block: 'nearest',
                    behavior: 'smooth'
                });
            }
        },
        
        // Search functionality
        get filteredItems() {
            if (!this.config.searchable || !this.searchTerm.trim()) {
                return this.items;
            }
            
            return this.items.filter(item => {
                const searchText = typeof item === 'string' ? item : item.text || item.label || item.name;
                return searchText.toLowerCase().includes(this.searchTerm.toLowerCase());
            });
        },
        
        clearSearch() {
            this.searchTerm = '';
        },
        
        // Utility methods
        isItemSelected(index) {
            return this.selectedIndex === index;
        },
        
        isItemDisabled(item) {
            return item.disabled || false;
        },
        
        getItemText(item) {
            return typeof item === 'string' ? item : item.text || item.label || item.name;
        },
        
        getItemValue(item) {
            return typeof item === 'string' ? item : item.value || item.id;
        },
        
        // Error handling
        setError(message) {
            this.error = message;
        },
        
        clearError() {
            this.error = null;
        },
        
        // Loading state
        setLoading(state) {
            this.loading = !!state;
        }
    };
}

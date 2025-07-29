/**
 * Table Customization System
 * Handles column visibility, reordering, sorting, and user preferences
 */
class TableCustomizer {
    constructor(tableSelector, options = {}) {
        this.tableSelector = tableSelector;
        this.table = document.querySelector(tableSelector);
        
        this.options = {
            storageKey: 'hdtickets_table_prefs',
            enableColumnReorder: true,
            enableColumnResize: true,
            enableColumnToggle: true,
            enableSort: true,
            enableFilters: true,
            minColumnWidth: 80,
            maxColumnWidth: 400,
            ...options
        };

        this.columns = [];
        this.preferences = {};
        this.sortState = { column: null, direction: 'asc' };
        this.filters = {};
        
        if (this.table) {
            this.init();
        }
    }

    init() {
        this.parseColumns();
        this.loadPreferences();
        this.setupColumnControls();
        this.setupSorting();
        this.setupFiltering();
        this.setupResponsive();
        this.applyPreferences();
        this.attachEventListeners();
    }

    parseColumns() {
        const headers = this.table.querySelectorAll('thead th');
        this.columns = Array.from(headers).map((header, index) => ({
            id: header.dataset.column || `col_${index}`,
            label: header.textContent.trim(),
            visible: !header.classList.contains('hidden'),
            sortable: !header.classList.contains('no-sort'),
            filterable: !header.classList.contains('no-filter'),
            resizable: !header.classList.contains('no-resize'),
            width: header.offsetWidth,
            minWidth: parseInt(header.dataset.minWidth) || this.options.minColumnWidth,
            maxWidth: parseInt(header.dataset.maxWidth) || this.options.maxColumnWidth,
            order: index,
            element: header
        }));
    }

    setupColumnControls() {
        // Create column control panel
        const controlPanel = this.createControlPanel();
        this.table.parentNode.insertBefore(controlPanel, this.table);

        // Add column toggle buttons
        this.createColumnToggles(controlPanel);
        
        // Add column reorder handles if enabled
        if (this.options.enableColumnReorder) {
            this.setupColumnReorder();
        }

        // Add column resize handles if enabled
        if (this.options.enableColumnResize) {
            this.setupColumnResize();
        }
    }

    createControlPanel() {
        const panel = document.createElement('div');
        panel.className = 'table-controls bg-white border border-gray-200 rounded-lg p-4 mb-4';
        panel.innerHTML = `
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <h3 class="text-sm font-medium text-gray-700">Table Options</h3>
                    <button type="button" class="reset-table-btn btn-secondary text-xs">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </button>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" class="column-settings-btn btn-secondary text-xs">
                        <i class="fas fa-columns mr-1"></i>Columns
                    </button>
                    <button type="button" class="export-table-btn btn-secondary text-xs">
                        <i class="fas fa-download mr-1"></i>Export
                    </button>
                </div>
            </div>
            <div class="column-toggles mt-3" style="display: none;"></div>
        `;
        return panel;
    }

    createColumnToggles(panel) {
        const togglesContainer = panel.querySelector('.column-toggles');
        
        const togglesHTML = this.columns.map(column => `
            <label class="inline-flex items-center mr-4 mb-2">
                <input type="checkbox" 
                       class="column-toggle rounded border-gray-300 text-blue-600 focus:border-blue-300 focus:ring focus:ring-blue-200" 
                       data-column="${column.id}" 
                       ${column.visible ? 'checked' : ''}>
                <span class="ml-2 text-sm text-gray-700">${column.label}</span>
            </label>
        `).join('');

        togglesContainer.innerHTML = `
            <div class="border-t pt-3">
                <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Visible Columns</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                    ${togglesHTML}
                </div>
            </div>
        `;
    }

    setupSorting() {
        if (!this.options.enableSort) return;

        this.columns.forEach(column => {
            if (column.sortable) {
                const header = column.element;
                header.classList.add('sortable', 'cursor-pointer', 'select-none');
                header.innerHTML += `<i class="fas fa-sort sort-icon ml-2 text-gray-400"></i>`;
                
                header.addEventListener('click', () => {
                    this.toggleSort(column.id);
                });
            }
        });
    }

    setupFiltering() {
        if (!this.options.enableFilters) return;

        // Add filter row
        const filterRow = document.createElement('tr');
        filterRow.className = 'filter-row bg-gray-50';
        
        this.columns.forEach(column => {
            const filterCell = document.createElement('th');
            filterCell.className = 'px-4 py-2 border-b';
            
            if (column.filterable) {
                filterCell.innerHTML = `
                    <input type="text" 
                           class="column-filter form-input text-xs w-full" 
                           data-column="${column.id}"
                           placeholder="Filter ${column.label}">
                `;
            }
            
            filterRow.appendChild(filterCell);
        });

        this.table.querySelector('thead').appendChild(filterRow);
    }

    setupColumnReorder() {
        // Add drag handles to headers
        this.columns.forEach(column => {
            const handle = document.createElement('span');
            handle.className = 'drag-handle cursor-move text-gray-400 hover:text-gray-600 mr-2';
            handle.innerHTML = '<i class="fas fa-grip-vertical"></i>';
            column.element.insertBefore(handle, column.element.firstChild);
        });

        // Setup sortable functionality
        this.setupSortableHeaders();
    }

    setupColumnResize() {
        this.columns.forEach(column => {
            if (column.resizable) {
                const resizer = document.createElement('div');
                resizer.className = 'column-resizer absolute right-0 top-0 w-1 h-full cursor-col-resize bg-transparent hover:bg-blue-500 hover:opacity-50';
                
                column.element.style.position = 'relative';
                column.element.appendChild(resizer);
                
                this.setupResizer(resizer, column);
            }
        });
    }

    setupResizer(resizer, column) {
        let startX, startWidth;
        
        resizer.addEventListener('mousedown', (e) => {
            startX = e.clientX;
            startWidth = parseInt(document.defaultView.getComputedStyle(column.element).width, 10);
            document.addEventListener('mousemove', doResize);
            document.addEventListener('mouseup', stopResize);
            e.preventDefault();
        });

        const doResize = (e) => {
            const newWidth = Math.max(
                column.minWidth,
                Math.min(column.maxWidth, startWidth + e.clientX - startX)
            );
            column.element.style.width = newWidth + 'px';
            column.width = newWidth;
        };

        const stopResize = () => {
            document.removeEventListener('mousemove', doResize);
            document.removeEventListener('mouseup', stopResize);
            this.savePreferences();
        };
    }

    setupResponsive() {
        // Setup responsive behavior
        const handleResize = () => {
            const tableWidth = this.table.offsetWidth;
            const containerWidth = this.table.parentElement.offsetWidth;
            
            if (tableWidth > containerWidth) {
                this.table.classList.add('table-responsive');
            } else {
                this.table.classList.remove('table-responsive');
            }
        };

        window.addEventListener('resize', handleResize);
        handleResize();
    }

    attachEventListeners() {
        const panel = this.table.parentNode.querySelector('.table-controls');
        
        // Column settings toggle
        panel.querySelector('.column-settings-btn')?.addEventListener('click', () => {
            const toggles = panel.querySelector('.column-toggles');
            toggles.style.display = toggles.style.display === 'none' ? 'block' : 'none';
        });

        // Reset table
        panel.querySelector('.reset-table-btn')?.addEventListener('click', () => {
            this.resetTable();
        });

        // Export table
        panel.querySelector('.export-table-btn')?.addEventListener('click', () => {
            this.exportTable();
        });

        // Column toggles
        panel.querySelectorAll('.column-toggle').forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                this.toggleColumn(e.target.dataset.column, e.target.checked);
            });
        });

        // Column filters
        this.table.querySelectorAll('.column-filter').forEach(filter => {
            filter.addEventListener('input', this.debounce((e) => {
                this.setFilter(e.target.dataset.column, e.target.value);
            }, 300));
        });
    }

    toggleColumn(columnId, visible) {
        const column = this.columns.find(col => col.id === columnId);
        if (!column) return;

        column.visible = visible;
        
        // Toggle header visibility
        column.element.style.display = visible ? '' : 'none';
        
        // Toggle all cells in this column
        const columnIndex = this.columns.indexOf(column);
        this.table.querySelectorAll(`tbody tr`).forEach(row => {
            const cell = row.cells[columnIndex];
            if (cell) {
                cell.style.display = visible ? '' : 'none';
            }
        });

        this.savePreferences();
    }

    toggleSort(columnId) {
        const column = this.columns.find(col => col.id === columnId);
        if (!column || !column.sortable) return;

        // Update sort state
        if (this.sortState.column === columnId) {
            this.sortState.direction = this.sortState.direction === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortState.column = columnId;
            this.sortState.direction = 'asc';
        }

        // Update UI
        this.updateSortUI();
        
        // Apply sort
        this.applySorting();
        
        this.savePreferences();
    }

    updateSortUI() {
        // Reset all sort icons
        this.table.querySelectorAll('.sort-icon').forEach(icon => {
            icon.className = 'fas fa-sort sort-icon ml-2 text-gray-400';
        });

        // Update active sort icon
        if (this.sortState.column) {
            const column = this.columns.find(col => col.id === this.sortState.column);
            if (column) {
                const icon = column.element.querySelector('.sort-icon');
                if (icon) {
                    icon.className = `fas fa-sort-${this.sortState.direction === 'asc' ? 'up' : 'down'} sort-icon ml-2 text-blue-600`;
                }
            }
        }
    }

    applySorting() {
        if (!this.sortState.column) return;

        const tbody = this.table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = this.columns.findIndex(col => col.id === this.sortState.column);

        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex]?.textContent.trim() || '';
            const bValue = b.cells[columnIndex]?.textContent.trim() || '';
            
            let comparison = 0;
            
            // Try numeric comparison first
            const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                comparison = aNum - bNum;
            } else {
                comparison = aValue.localeCompare(bValue);
            }
            
            return this.sortState.direction === 'asc' ? comparison : -comparison;
        });

        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    }

    setFilter(columnId, value) {
        this.filters[columnId] = value.toLowerCase();
        this.applyFilters();
        this.savePreferences();
    }

    applyFilters() {
        const rows = this.table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            let visible = true;
            
            Object.entries(this.filters).forEach(([columnId, filterValue]) => {
                if (!filterValue) return;
                
                const columnIndex = this.columns.findIndex(col => col.id === columnId);
                const cellValue = row.cells[columnIndex]?.textContent.toLowerCase() || '';
                
                if (!cellValue.includes(filterValue)) {
                    visible = false;
                }
            });
            
            row.style.display = visible ? '' : 'none';
        });
    }

    resetTable() {
        // Reset all preferences
        this.preferences = {};
        this.sortState = { column: null, direction: 'asc' };
        this.filters = {};
        
        // Reset UI
        this.columns.forEach(column => {
            column.visible = true;
            column.element.style.display = '';
            column.element.style.width = '';
        });
        
        // Reset filters
        this.table.querySelectorAll('.column-filter').forEach(filter => {
            filter.value = '';
        });
        
        // Reset sorting
        this.updateSortUI();
        
        // Show all rows
        this.table.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = '';
        });
        
        // Update toggles
        this.table.parentNode.querySelectorAll('.column-toggle').forEach(toggle => {
            toggle.checked = true;
        });
        
        this.savePreferences();
    }

    exportTable() {
        const visibleColumns = this.columns.filter(col => col.visible);
        const rows = Array.from(this.table.querySelectorAll('tbody tr')).filter(row => 
            row.style.display !== 'none'
        );

        // Create CSV content
        const headers = visibleColumns.map(col => col.label).join(',');
        const csvContent = [headers];
        
        rows.forEach(row => {
            const rowData = visibleColumns.map((col, index) => {
                const columnIndex = this.columns.indexOf(col);
                return `"${row.cells[columnIndex]?.textContent.trim() || ''}"`;
            }).join(',');
            csvContent.push(rowData);
        });

        // Download CSV
        const blob = new Blob([csvContent.join('\n')], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `table-export-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }

    loadPreferences() {
        const key = `${this.options.storageKey}_${this.getTableId()}`;
        try {
            const saved = localStorage.getItem(key);
            if (saved) {
                this.preferences = JSON.parse(saved);
                
                // Apply saved preferences
                if (this.preferences.columns) {
                    Object.entries(this.preferences.columns).forEach(([columnId, prefs]) => {
                        const column = this.columns.find(col => col.id === columnId);
                        if (column) {
                            Object.assign(column, prefs);
                        }
                    });
                }
                
                if (this.preferences.sort) {
                    this.sortState = this.preferences.sort;
                }
                
                if (this.preferences.filters) {
                    this.filters = this.preferences.filters;
                }
            }
        } catch (e) {
            console.warn('Failed to load table preferences:', e);
        }
    }

    savePreferences() {
        const key = `${this.options.storageKey}_${this.getTableId()}`;
        
        this.preferences = {
            columns: {},
            sort: this.sortState,
            filters: this.filters,
            timestamp: Date.now()
        };
        
        this.columns.forEach(column => {
            this.preferences.columns[column.id] = {
                visible: column.visible,
                width: column.width,
                order: column.order
            };
        });
        
        try {
            localStorage.setItem(key, JSON.stringify(this.preferences));
        } catch (e) {
            console.warn('Failed to save table preferences:', e);
        }
    }

    applyPreferences() {
        // Apply column visibility
        this.columns.forEach(column => {
            if (!column.visible) {
                this.toggleColumn(column.id, false);
            }
        });
        
        // Apply sorting
        if (this.sortState.column) {
            this.updateSortUI();
            this.applySorting();
        }
        
        // Apply filters
        Object.entries(this.filters).forEach(([columnId, value]) => {
            const filter = this.table.querySelector(`[data-column="${columnId}"].column-filter`);
            if (filter) {
                filter.value = value;
            }
        });
        
        if (Object.keys(this.filters).length > 0) {
            this.applyFilters();
        }
    }

    getTableId() {
        return this.table.id || this.table.className || 'default';
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Public API
    getVisibleColumns() {
        return this.columns.filter(col => col.visible);
    }

    hideColumn(columnId) {
        this.toggleColumn(columnId, false);
    }

    showColumn(columnId) {
        this.toggleColumn(columnId, true);
    }

    sortBy(columnId, direction = 'asc') {
        this.sortState = { column: columnId, direction };
        this.updateSortUI();
        this.applySorting();
        this.savePreferences();
    }

    filterBy(columnId, value) {
        this.setFilter(columnId, value);
    }

    clearFilters() {
        this.filters = {};
        this.table.querySelectorAll('.column-filter').forEach(filter => {
            filter.value = '';
        });
        this.applyFilters();
        this.savePreferences();
    }
}

// Auto-initialize if not in module environment
if (typeof module === 'undefined') {
    window.TableCustomizer = TableCustomizer;
}

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TableCustomizer;
}

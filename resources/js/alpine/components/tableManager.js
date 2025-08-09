/**
 * Table Manager Alpine.js Component
 * Handles table sorting, filtering, pagination, and row selection
 */
export default function tableManager() {
    return {
        // Data state
        data: [],
        filteredData: [],
        paginatedData: [],
        
        // Table state
        sortColumn: '',
        sortDirection: 'asc',
        currentPage: 1,
        itemsPerPage: 10,
        
        // Selection state
        selectedRows: new Set(),
        selectAll: false,
        
        // Filter state
        searchQuery: '',
        filters: {},
        
        // Loading state
        loading: false,
        error: null,
        
        init() {
            // Initialize table
            this.loadData();
            
            // Watch for changes
            this.$watch('searchQuery', () => this.applyFilters());
            this.$watch('filters', () => this.applyFilters(), { deep: true });
            this.$watch('selectAll', (value) => this.toggleAllRows(value));
        },
        
        async loadData(url = null) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch(url || this.getDataUrl(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                this.data = result.data || result;
                this.applyFilters();
                
            } catch (error) {
                this.error = error.message;
                console.error('Table data loading failed:', error);
            } finally {
                this.loading = false;
            }
        },
        
        getDataUrl() {
            return this.$el.dataset.url || `${window.location.pathname}/data`;
        },
        
        applyFilters() {
            let filtered = [...this.data];
            
            // Apply search query
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(row => 
                    Object.values(row).some(value => 
                        String(value).toLowerCase().includes(query)
                    )
                );
            }
            
            // Apply column filters
            Object.entries(this.filters).forEach(([column, filterValue]) => {
                if (filterValue !== null && filterValue !== '') {
                    filtered = filtered.filter(row => {
                        const cellValue = String(row[column]).toLowerCase();
                        const filter = String(filterValue).toLowerCase();
                        return cellValue.includes(filter);
                    });
                }
            });
            
            this.filteredData = filtered;
            this.currentPage = 1; // Reset to first page
            this.updatePagination();
            this.updateSelectAll();
        },
        
        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            
            this.filteredData.sort((a, b) => {
                let valueA = a[column];
                let valueB = b[column];
                
                // Handle null/undefined values
                if (valueA == null) valueA = '';
                if (valueB == null) valueB = '';
                
                // Convert to strings for comparison
                valueA = String(valueA).toLowerCase();
                valueB = String(valueB).toLowerCase();
                
                // Numeric sorting if both values are numbers
                const numA = parseFloat(valueA);
                const numB = parseFloat(valueB);
                if (!isNaN(numA) && !isNaN(numB)) {
                    valueA = numA;
                    valueB = numB;
                }
                
                let comparison = 0;
                if (valueA > valueB) comparison = 1;
                if (valueA < valueB) comparison = -1;
                
                return this.sortDirection === 'desc' ? comparison * -1 : comparison;
            });
            
            this.updatePagination();
        },
        
        updatePagination() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            this.paginatedData = this.filteredData.slice(start, end);
        },
        
        goToPage(page) {
            const totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
            if (page >= 1 && page <= totalPages) {
                this.currentPage = page;
                this.updatePagination();
            }
        },
        
        nextPage() {
            this.goToPage(this.currentPage + 1);
        },
        
        prevPage() {
            this.goToPage(this.currentPage - 1);
        },
        
        toggleRowSelection(rowId, event) {
            if (event?.shiftKey && this.selectedRows.size > 0) {
                this.selectRange(rowId);
            } else {
                if (this.selectedRows.has(rowId)) {
                    this.selectedRows.delete(rowId);
                } else {
                    this.selectedRows.add(rowId);
                }
                this.updateSelectAll();
            }
        },
        
        toggleAllRows(selectAll) {
            if (selectAll) {
                this.paginatedData.forEach(row => {
                    this.selectedRows.add(this.getRowId(row));
                });
            } else {
                this.paginatedData.forEach(row => {
                    this.selectedRows.delete(this.getRowId(row));
                });
            }
        },
        
        updateSelectAll() {
            const visibleRowIds = this.paginatedData.map(row => this.getRowId(row));
            const selectedVisible = visibleRowIds.filter(id => this.selectedRows.has(id));
            
            this.selectAll = visibleRowIds.length > 0 && selectedVisible.length === visibleRowIds.length;
        },
        
        getRowId(row) {
            return row.id || row.uuid || JSON.stringify(row);
        },
        
        isRowSelected(row) {
            return this.selectedRows.has(this.getRowId(row));
        },
        
        clearSelection() {
            this.selectedRows.clear();
            this.selectAll = false;
        },
        
        refresh() {
            this.loadData();
        },
        
        // Computed properties
        get totalPages() {
            return Math.ceil(this.filteredData.length / this.itemsPerPage);
        },
        
        get hasNextPage() {
            return this.currentPage < this.totalPages;
        },
        
        get hasPrevPage() {
            return this.currentPage > 1;
        },
        
        get selectedCount() {
            return this.selectedRows.size;
        }
    };
}

<template>
  <div class="mobile-responsive-table" :class="{ 'mobile-mode': isMobile }">
    <!-- Desktop/Tablet Table View -->
    <div v-if="!isMobile || forceTableView" class="table-container">
      <div class="table-wrapper" ref="tableWrapper">
        <table class="responsive-table">
          <thead>
            <tr>
              <th 
                v-for="column in columns" 
                :key="column.key"
                :class="[
                  'table-header',
                  column.sortable ? 'sortable' : '',
                  sortBy === column.key ? 'sorted' : ''
                ]"
                @click="column.sortable && handleSort(column.key)"
              >
                <div class="header-content">
                  <span>{{ column.label }}</span>
                  <span v-if="column.sortable" class="sort-icon">
                    <i :class="getSortIcon(column.key)"></i>
                  </span>
                </div>
              </th>
              <th v-if="hasActions" class="actions-header">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(row, index) in sortedData" 
              :key="row.id || index"
              class="table-row"
              :class="{ 'selected': selectedRows.includes(row.id) }"
              @click="handleRowClick(row)"
            >
              <td 
                v-for="column in columns" 
                :key="column.key"
                :class="['table-cell', column.class]"
                :data-label="column.label"
              >
                <div class="cell-content">
                  <slot 
                    :name="`cell-${column.key}`" 
                    :row="row" 
                    :value="getNestedValue(row, column.key)"
                    :column="column"
                  >
                    <span v-if="column.type === 'badge'" 
                          :class="`badge badge-${getBadgeType(getNestedValue(row, column.key))}`">
                      {{ formatValue(getNestedValue(row, column.key), column) }}
                    </span>
                    <span v-else-if="column.type === 'currency'" class="currency">
                      {{ formatCurrency(getNestedValue(row, column.key)) }}
                    </span>
                    <span v-else-if="column.type === 'date'" class="date">
                      {{ formatDate(getNestedValue(row, column.key)) }}
                    </span>
                    <span v-else-if="column.type === 'boolean'" class="boolean">
                      <i :class="getNestedValue(row, column.key) ? 'icon-check text-green-600' : 'icon-x text-red-600'"></i>
                    </span>
                    <span v-else>
                      {{ formatValue(getNestedValue(row, column.key), column) }}
                    </span>
                  </slot>
                </div>
              </td>
              <td v-if="hasActions" class="actions-cell">
                <div class="actions-container">
                  <slot name="actions" :row="row" :index="index">
                    <button class="action-btn" @click.stop="$emit('edit', row)">
                      <i class="icon-edit"></i>
                    </button>
                    <button class="action-btn delete" @click.stop="$emit('delete', row)">
                      <i class="icon-trash"></i>
                    </button>
                  </slot>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Card View -->
    <div v-else class="mobile-cards">
      <div 
        v-for="(row, index) in sortedData" 
        :key="row.id || index"
        class="mobile-card"
        :class="{ 'selected': selectedRows.includes(row.id) }"
        @click="handleRowClick(row)"
      >
        <div class="card-header">
          <slot name="card-header" :row="row" :index="index">
            <h4 class="card-title">{{ getCardTitle(row) }}</h4>
            <div class="card-status">
              <slot name="card-status" :row="row">
                <span v-if="statusColumn" 
                      :class="`badge badge-${getBadgeType(getNestedValue(row, statusColumn))}`">
                  {{ formatValue(getNestedValue(row, statusColumn), getColumn(statusColumn)) }}
                </span>
              </slot>
            </div>
          </slot>
        </div>

        <div class="card-body">
          <div 
            v-for="column in visibleColumns" 
            :key="column.key"
            class="card-field"
            v-show="!column.hideInCard"
          >
            <label class="field-label">{{ column.label }}</label>
            <div class="field-value">
              <slot 
                :name="`card-${column.key}`" 
                :row="row" 
                :value="getNestedValue(row, column.key)"
                :column="column"
              >
                <span v-if="column.type === 'badge'" 
                      :class="`badge badge-${getBadgeType(getNestedValue(row, column.key))}`">
                  {{ formatValue(getNestedValue(row, column.key), column) }}
                </span>
                <span v-else-if="column.type === 'currency'" class="currency">
                  {{ formatCurrency(getNestedValue(row, column.key)) }}
                </span>
                <span v-else-if="column.type === 'date'" class="date">
                  {{ formatDate(getNestedValue(row, column.key)) }}
                </span>
                <span v-else-if="column.type === 'boolean'" class="boolean">
                  <i :class="getNestedValue(row, column.key) ? 'icon-check text-green-600' : 'icon-x text-red-600'"></i>
                </span>
                <span v-else>
                  {{ formatValue(getNestedValue(row, column.key), column) }}
                </span>
              </slot>
            </div>
          </div>
        </div>

        <div v-if="hasActions" class="card-actions">
          <slot name="card-actions" :row="row" :index="index">
            <div class="action-buttons">
              <button class="action-btn primary" @click.stop="$emit('edit', row)">
                <i class="icon-edit"></i>
                <span>Edit</span>
              </button>
              <button class="action-btn danger" @click.stop="$emit('delete', row)">
                <i class="icon-trash"></i>
                <span>Delete</span>
              </button>
            </div>
          </slot>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="sortedData.length === 0" class="empty-state">
      <slot name="empty">
        <div class="empty-content">
          <i class="icon-database text-4xl text-gray-400"></i>
          <h3>No data available</h3>
          <p>There are no items to display at the moment.</p>
        </div>
      </slot>
    </div>

    <!-- Mobile View Toggle -->
    <div class="view-toggle" v-if="showViewToggle">
      <button 
        class="toggle-btn" 
        :class="{ active: !isMobile || forceTableView }"
        @click="forceTableView = true"
      >
        <i class="icon-table"></i>
        <span>Table</span>
      </button>
      <button 
        class="toggle-btn" 
        :class="{ active: isMobile && !forceTableView }"
        @click="forceTableView = false"
      >
        <i class="icon-grid"></i>
        <span>Cards</span>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';

export default {
  name: 'MobileResponsiveTable',
  emits: ['sort', 'row-click', 'edit', 'delete', 'selection-change'],
  props: {
    data: {
      type: Array,
      required: true,
      default: () => []
    },
    columns: {
      type: Array,
      required: true,
      default: () => []
    },
    sortBy: {
      type: String,
      default: ''
    },
    sortOrder: {
      type: String,
      default: 'asc',
      validator: value => ['asc', 'desc'].includes(value)
    },
    selectable: {
      type: Boolean,
      default: false
    },
    selectedRows: {
      type: Array,
      default: () => []
    },
    hasActions: {
      type: Boolean,
      default: true
    },
    statusColumn: {
      type: String,
      default: 'status'
    },
    titleColumn: {
      type: String,
      default: 'title'
    },
    breakpoint: {
      type: Number,
      default: 768
    },
    showViewToggle: {
      type: Boolean,
      default: true
    }
  },
  setup(props, { emit }) {
    const tableWrapper = ref(null);
    const isMobile = ref(false);
    const forceTableView = ref(false);

    // Computed properties
    const visibleColumns = computed(() => {
      return props.columns.filter(col => !col.hidden);
    });

    const sortedData = computed(() => {
      if (!props.sortBy) {
        return props.data;
      }

      return [...props.data].sort((a, b) => {
        const aVal = getNestedValue(a, props.sortBy);
        const bVal = getNestedValue(b, props.sortBy);
        
        let comparison = 0;
        if (aVal > bVal) comparison = 1;
        if (aVal < bVal) comparison = -1;
        
        return props.sortOrder === 'desc' ? -comparison : comparison;
      });
    });

    // Methods
    const checkIsMobile = () => {
      isMobile.value = window.innerWidth < props.breakpoint;
    };

    const getNestedValue = (obj, path) => {
      return path.split('.').reduce((o, p) => o && o[p], obj);
    };

    const getColumn = (key) => {
      return props.columns.find(col => col.key === key);
    };

    const getCardTitle = (row) => {
      return getNestedValue(row, props.titleColumn) || 'Untitled';
    };

    const formatValue = (value, column) => {
      if (value === null || value === undefined) return '-';
      
      if (column && column.formatter) {
        return column.formatter(value);
      }
      
      return value;
    };

    const formatCurrency = (value) => {
      if (!value) return '-';
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(value);
    };

    const formatDate = (value) => {
      if (!value) return '-';
      return new Date(value).toLocaleDateString();
    };

    const getBadgeType = (value) => {
      if (!value) return 'default';
      
      const statusMap = {
        'active': 'success',
        'inactive': 'danger',
        'pending': 'warning',
        'completed': 'success',
        'cancelled': 'danger',
        'processing': 'info',
        'available': 'success',
        'sold': 'danger',
        'reserved': 'warning'
      };
      
      return statusMap[value.toLowerCase()] || 'default';
    };

    const getSortIcon = (columnKey) => {
      if (props.sortBy !== columnKey) {
        return 'icon-chevron-up-down';
      }
      return props.sortOrder === 'asc' ? 'icon-chevron-up' : 'icon-chevron-down';
    };

    const handleSort = (columnKey) => {
      let newOrder = 'asc';
      if (props.sortBy === columnKey && props.sortOrder === 'asc') {
        newOrder = 'desc';
      }
      
      emit('sort', { column: columnKey, order: newOrder });
    };

    const handleRowClick = (row) => {
      emit('row-click', row);
    };

    // Lifecycle
    onMounted(() => {
      checkIsMobile();
      window.addEventListener('resize', checkIsMobile);
      
      // Setup horizontal scroll indicators for mobile tables
      if (tableWrapper.value) {
        const wrapper = tableWrapper.value;
        wrapper.addEventListener('scroll', () => {
          const { scrollLeft, scrollWidth, clientWidth } = wrapper;
          wrapper.classList.toggle('scroll-left', scrollLeft > 0);
          wrapper.classList.toggle('scroll-right', scrollLeft < scrollWidth - clientWidth);
        });
      }
    });

    onUnmounted(() => {
      window.removeEventListener('resize', checkIsMobile);
    });

    return {
      tableWrapper,
      isMobile,
      forceTableView,
      visibleColumns,
      sortedData,
      getNestedValue,
      getColumn,
      getCardTitle,
      formatValue,
      formatCurrency,
      formatDate,
      getBadgeType,
      getSortIcon,
      handleSort,
      handleRowClick
    };
  }
};
</script>

<style scoped>
.mobile-responsive-table {
  width: 100%;
}

/* Table Styles */
.table-container {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.table-wrapper {
  overflow-x: auto;
  position: relative;
}

.table-wrapper.scroll-left::before,
.table-wrapper.scroll-right::after {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  width: 20px;
  pointer-events: none;
  z-index: 1;
}

.table-wrapper.scroll-left::before {
  left: 0;
  background: linear-gradient(to right, rgba(255, 255, 255, 0.8), transparent);
}

.table-wrapper.scroll-right::after {
  right: 0;
  background: linear-gradient(to left, rgba(255, 255, 255, 0.8), transparent);
}

.responsive-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.table-header {
  background: #f8fafc;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
  position: sticky;
  top: 0;
  z-index: 10;
}

.table-header.sortable {
  cursor: pointer;
  user-select: none;
  transition: background-color 0.2s;
}

.table-header.sortable:hover {
  background: #e5e7eb;
}

.table-header.sorted {
  background: #dbeafe;
  color: #1d4ed8;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sort-icon {
  margin-left: 0.5rem;
  opacity: 0.6;
}

.table-row {
  transition: background-color 0.2s;
}

.table-row:hover {
  background: #f9fafb;
}

.table-row.selected {
  background: #eff6ff;
}

.table-cell {
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: top;
}

.cell-content {
  display: flex;
  align-items: center;
}

/* Mobile Card Styles */
.mobile-cards {
  display: grid;
  gap: 1rem;
  padding: 1rem;
}

.mobile-card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  transition: all 0.2s;
  border: 1px solid #e5e7eb;
}

.mobile-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.mobile-card.selected {
  border-color: #3b82f6;
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
  flex: 1;
  margin-right: 1rem;
}

.card-body {
  display: grid;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.card-field {
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 2rem;
}

.field-label {
  font-weight: 500;
  color: #6b7280;
  font-size: 0.875rem;
  flex: 0 0 40%;
}

.field-value {
  flex: 1;
  text-align: right;
  font-weight: 500;
  color: #111827;
}

.card-actions {
  border-top: 1px solid #f3f4f6;
  padding-top: 0.75rem;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

/* Action Buttons */
.actions-container {
  display: flex;
  gap: 0.25rem;
  justify-content: flex-end;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  min-width: 44px;
  padding: 0.5rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  gap: 0.5rem;
}

.action-btn:hover {
  transform: translateY(-1px);
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
}

.action-btn.primary:hover {
  background: #2563eb;
}

.action-btn.danger {
  background: #ef4444;
  color: white;
}

.action-btn.danger:hover {
  background: #dc2626;
}

.action-btn:not(.primary):not(.danger) {
  background: #f3f4f6;
  color: #374151;
}

.action-btn:not(.primary):not(.danger):hover {
  background: #e5e7eb;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge-danger {
  background: #fee2e2;
  color: #991b1b;
}

.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

.badge-info {
  background: #dbeafe;
  color: #1e40af;
}

.badge-default {
  background: #f3f4f6;
  color: #374151;
}

/* View Toggle */
.view-toggle {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 1rem;
}

.toggle-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background: white;
  color: #6b7280;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  min-height: 44px;
}

.toggle-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.toggle-btn.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #6b7280;
}

.empty-content h3 {
  margin: 1rem 0 0.5rem;
  color: #374151;
}

/* Responsive Adjustments */
@media (max-width: 640px) {
  .mobile-cards {
    padding: 0.5rem;
  }
  
  .mobile-card {
    padding: 0.75rem;
  }
  
  .card-field {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
  
  .field-label {
    flex: none;
  }
  
  .field-value {
    text-align: left;
  }
  
  .action-buttons {
    justify-content: stretch;
  }
  
  .action-btn {
    flex: 1;
  }
}

/* Touch-friendly improvements */
@media (hover: none) {
  .table-row:hover {
    background: transparent;
  }
  
  .mobile-card:hover {
    transform: none;
  }
  
  .action-btn:hover {
    transform: none;
  }
}
</style>

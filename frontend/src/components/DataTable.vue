<template>
  <div class="data-table" :class="{ 'dark-theme': isDarkTheme }">
      <div class="table-header">
        <div class="table-search">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="search-input"
          />
          <span class="search-icon">🔍</span>
        </div>
        <slot name="header-actions"></slot>
      </div>

    <div v-if="loading" class="table-loading">
      <div class="spinner"></div>
      <p>Loading...</p>
    </div>

    <div v-else-if="filteredData.length === 0" class="table-empty">
      <span class="empty-icon">📭</span>
      <p>{{ emptyMessage }}</p>
    </div>

    <div v-else class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="{ sortable: column.sortable }"
              @click="column.sortable && handleSort(column.key)"
            >
              <div class="th-content">
                <span>{{ column.label }}</span>
                <span v-if="column.sortable" class="sort-icon">
                  <span v-if="sortKey === column.key">
                    {{ sortOrder === 'asc' ? '↑' : '↓' }}
                  </span>
                  <span v-else>⇅</span>
                </span>
              </div>
            </th>
            <th v-if="hasActions" class="actions-column">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedData" :key="getRowKey(row, index)">
            <td v-for="column in columns" :key="column.key">
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="getNestedValue(row, column.key)"
              >
                <span v-if="column.type === 'boolean'">
                  <span :class="['badge', getNestedValue(row, column.key) ? 'badge-success' : 'badge-secondary']">
                    {{ getNestedValue(row, column.key) ? 'Yes' : 'No' }}
                  </span>
                </span>
                <span v-else-if="column.type === 'date'">
                  {{ formatDate(getNestedValue(row, column.key)) }}
                </span>
                <span v-else-if="column.type === 'currency'">
                  {{ formatCurrency(getNestedValue(row, column.key)) }}
                </span>
                <span v-else>{{ getNestedValue(row, column.key) || '-' }}</span>
              </slot>
            </td>
            <td v-if="hasActions" class="actions-cell">
              <slot name="actions" :row="row">
                <button
                  v-if="onEdit"
                  @click="onEdit(row)"
                  class="btn-icon btn-edit"
                  title="Edit"
                >
                  ✏️
                </button>
                <button
                  v-if="onDelete"
                  @click="onDelete(row)"
                  class="btn-icon btn-delete"
                  title="Delete"
                >
                  🗑️
                </button>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="filteredData.length > 0 && pagination" class="table-pagination">
      <div class="pagination-info">
        Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredData.length }} entries
      </div>
      <div class="pagination-controls">
        <button
          @click="currentPage = 1"
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          ««
        </button>
        <button
          @click="currentPage--"
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          ‹
        </button>
        <span class="pagination-page">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          @click="currentPage++"
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          ›
        </button>
        <button
          @click="currentPage = totalPages"
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          »»
        </button>
      </div>
      <div class="pagination-size">
        <label>
          Per page:
          <select v-model="pageSizeModel" class="page-size-select">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  emptyMessage: {
    type: String,
    default: 'No data available'
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Boolean,
    default: true
  },
  pageSize: {
    type: Number,
    default: 10
  },
  onEdit: {
    type: Function,
    default: null
  },
  onDelete: {
    type: Function,
    default: null
  },
  searchFields: {
    type: Array,
    default: () => []
  },
  rowKey: {
    type: String,
    default: 'id'
  }
})

const searchQuery = ref('')
const currentPage = ref(1)
const pageSizeModel = ref(props.pageSize)
const sortKey = ref(null)
const sortOrder = ref('asc')

// Watch prop changes and update local ref
watch(() => props.pageSize, (newVal) => {
  pageSizeModel.value = newVal
})

const hasActions = computed(() => props.onEdit || props.onDelete || !!$slots.actions)

// Determine if we should use dark theme (super admin or admin pages)
const isDarkTheme = computed(() => {
  return route.path.startsWith('/super-admin') || route.path.startsWith('/admin')
})

const getRowKey = (row, index) => {
  return row[props.rowKey] || index
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, prop) => current?.[prop], obj)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '-'
  return `€${parseFloat(value).toFixed(2)}`
}

const filteredData = computed(() => {
  let result = [...props.data]

  // Search filter
  if (searchQuery.value && props.searchFields.length > 0) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(row => {
      return props.searchFields.some(field => {
        const value = getNestedValue(row, field)
        return value && String(value).toLowerCase().includes(query)
      })
    })
  }

  // Sort
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = getNestedValue(a, sortKey.value)
      const bVal = getNestedValue(b, sortKey.value)
      
      if (aVal === bVal) return 0
      
      const comparison = aVal > bVal ? 1 : -1
      return sortOrder.value === 'asc' ? comparison : -comparison
    })
  }

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredData.value.length / pageSizeModel.value)
})

const startIndex = computed(() => {
  return (currentPage.value - 1) * pageSizeModel.value
})

const endIndex = computed(() => {
  return Math.min(startIndex.value + pageSizeModel.value, filteredData.value.length)
})

const paginatedData = computed(() => {
  if (!props.pagination) {
    return filteredData.value
  }
  return filteredData.value.slice(startIndex.value, endIndex.value)
})

const handleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

watch(() => props.data, () => {
  currentPage.value = 1
})

watch(() => filteredData.value.length, () => {
  if (currentPage.value > totalPages.value && totalPages.value > 0) {
    currentPage.value = totalPages.value
  }
})
</script>

<style scoped>
.data-table {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.data-table.dark-theme {
  background: rgba(20, 20, 20, 0.8);
  border: 1px solid rgba(102, 126, 234, 0.3);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.table-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
  background: #f9fafb;
}

.data-table.dark-theme .table-header {
  border-bottom: 1px solid rgba(102, 126, 234, 0.2);
  background: rgba(0, 0, 0, 0.2);
}

.table-search {
  position: relative;
  flex: 1;
  min-width: 250px;
  max-width: 400px;
}

.search-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #1f2937;
  transition: all 0.2s;
}

.data-table.dark-theme .search-input {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  color: #e5e7eb;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: white;
}

.data-table.dark-theme .search-input:focus {
  background: rgba(0, 0, 0, 0.5);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-input::placeholder {
  color: #9ca3af;
}

.data-table.dark-theme .search-input::placeholder {
  color: #6b7280;
}

.search-icon {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #6b7280;
}

.data-table.dark-theme .search-icon {
  color: #9ca3af;
}

.table-wrapper {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.data-table.dark-theme .table thead {
  background: rgba(102, 126, 234, 0.1);
  border-bottom: 2px solid rgba(102, 126, 234, 0.3);
}

.table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5e7eb;
}

.data-table.dark-theme .table th {
  color: #d1d5db;
  border-bottom: 2px solid rgba(102, 126, 234, 0.2);
}

.table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: background 0.2s;
}

.table th.sortable:hover {
  background: #f3f4f6;
}

.data-table.dark-theme .table th.sortable:hover {
  background: rgba(102, 126, 234, 0.15);
}

.th-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-icon {
  font-size: 0.8rem;
  color: #667eea;
}

.table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
  font-size: 0.9rem;
}

.data-table.dark-theme .table td {
  border-bottom: 1px solid rgba(102, 126, 234, 0.1);
  color: #e5e7eb;
}

.table tbody tr {
  transition: background 0.2s;
}

.table tbody tr:hover {
  background: #f9fafb;
}

.data-table.dark-theme .table tbody tr:hover {
  background: rgba(102, 126, 234, 0.05);
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.actions-column {
  width: 120px;
  text-align: center;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-edit:hover {
  background: rgba(59, 130, 246, 0.2);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
}

.table-loading,
.table-empty {
  padding: 3rem;
  text-align: center;
  color: #6b7280;
}

.data-table.dark-theme .table-loading,
.data-table.dark-theme .table-empty {
  color: #9ca3af;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(102, 126, 234, 0.2);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-success {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.badge-secondary {
  background: rgba(156, 163, 175, 0.2);
  color: #9ca3af;
  border: 1px solid rgba(156, 163, 175, 0.3);
}

.table-pagination {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  background: white;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.data-table.dark-theme .table-pagination {
  border-top: 1px solid rgba(102, 126, 234, 0.2);
  background: rgba(0, 0, 0, 0.1);
}

.pagination-info {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  color: #374151;
  transition: all 0.2s;
}

.data-table.dark-theme .pagination-btn {
  border: 1px solid rgba(102, 126, 234, 0.3);
  background: rgba(0, 0, 0, 0.3);
  color: #d1d5db;
}

.pagination-btn:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #667eea;
  color: #667eea;
}

.data-table.dark-theme .pagination-btn:hover:not(:disabled) {
  background: rgba(102, 126, 234, 0.2);
  border-color: #667eea;
  color: #e5e7eb;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-page {
  padding: 0 1rem;
  font-size: 0.9rem;
  color: #6b7280;
  transition: color 0.2s;
}

.data-table.dark-theme .pagination-page {
  color: #d1d5db;
}

.pagination-page:hover {
  color: #667eea;
}

.data-table.dark-theme .pagination-page:hover {
  color: #e5e7eb;
}

.pagination-page.active {
  color: #667eea;
  font-weight: 600;
}

.data-table.dark-theme .pagination-page.active {
  color: #e5e7eb;
}

.pagination-size {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.page-size-select {
  padding: 0.5rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #374151;
  transition: all 0.2s;
}

.data-table.dark-theme .page-size-select {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  color: #e5e7eb;
}

.page-size-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: white;
}

.data-table.dark-theme .page-size-select:focus {
  background: rgba(0, 0, 0, 0.5);
}

@media (max-width: 768px) {
  .table-header {
    flex-direction: column;
    align-items: stretch;
  }

  .table-search {
    max-width: 100%;
  }

  .table-pagination {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-controls {
    justify-content: center;
  }
}
</style>

<template>
  <SuperAdminLayout>
    <div class="bookings-page">
      <div class="page-header">
        <h1>Bookings Management</h1>
      </div>

      <DataTable
        :data="bookings"
        :columns="columns"
        :loading="loading"
        search-placeholder="Search bookings..."
        empty-message="No bookings found"
        :search-fields="['id', 'user.name', 'hotel.name']"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
      >
        <template #cell-user="{ value }">
          {{ value?.name || 'N/A' }} ({{ value?.email || '' }})
        </template>
        <template #cell-hotel="{ value }">
          {{ value?.name || 'N/A' }}
        </template>
        <template #cell-status="{ value }">
          <span class="status-badge" :class="`status-${value}`">{{ value }}</span>
        </template>
        <template #cell-totalPrice="{ value }">
          €{{ parseFloat(value || 0).toFixed(2) }}
        </template>
        <template #cell-dates="{ row }">
          {{ formatDate(row.startDate) }} - {{ formatDate(row.endDate) }}
        </template>
        <template #actions="{ row }">
          <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Edit">✏️</button>
          <button @click="handleDelete(row)" class="btn-icon btn-delete" title="Delete">🗑️</button>
        </template>
      </DataTable>

      <!-- Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content large">
            <div class="modal-header">
              <h2>Edit Booking</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Guest (User) *</label>
                <select v-model="form.users_id" required>
                  <option value="">Select user...</option>
                  <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Hotel *</label>
                <select v-model="form.hotels_id" required>
                  <option value="">Select hotel...</option>
                  <option v-for="hotel in availableHotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name }} - {{ hotel.location }}
                  </option>
                </select>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Start Date *</label>
                  <input v-model="form.startDate" type="date" required />
                </div>
                <div class="form-group">
                  <label>End Date *</label>
                  <input v-model="form.endDate" type="date" required />
                </div>
              </div>

              <div class="form-group">
                <label>Total Price (€) *</label>
                <input v-model.number="form.totalPrice" type="number" step="0.01" min="0" required />
              </div>

              <div class="form-group">
                <label>Status *</label>
                <select v-model="form.status" required>
                  <option value="pending">Pending</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="cancelled">Cancelled</option>
                  <option value="completed">Completed</option>
                </select>
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="saving">
                  {{ saving ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>

      <ConfirmDialog
        v-model:visible="showDeleteDialog"
        title="Delete Booking"
        :message="`Are you sure you want to delete booking #${bookingToDelete?.id}? This action cannot be undone.`"
        confirm-text="Delete"
        cancel-text="Cancel"
        confirm-type="danger"
        @confirm="confirmDelete"
      />

      <Toast ref="toast" />
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import SuperAdminLayout from '../../layouts/SuperAdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { superAdminService } from '../../services/superAdminService'

const bookings = ref([])
const availableUsers = ref([])
const availableHotels = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingBooking = ref(null)
const bookingToDelete = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)

const form = ref({
  users_id: '',
  hotels_id: '',
  startDate: '',
  endDate: '',
  totalPrice: 0,
  status: 'pending'
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'user', label: 'Guest' },
  { key: 'hotel', label: 'Hotel' },
  { key: 'dates', label: 'Dates' },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'totalPrice', label: 'Total', sortable: true }
]

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('hu-HU')
}

const loadBookings = async () => {
  loading.value = true
  try {
    const data = await superAdminService.getAllBookings()
    bookings.value = data
  } catch (err) {
    showToast('Failed to load bookings', 'error')
  } finally {
    loading.value = false
  }
}

const loadUsers = async () => {
  try {
    const data = await superAdminService.getAllUsers()
    availableUsers.value = data.filter(u => u.role === 'user')
  } catch (err) {
    console.error('Failed to load users:', err)
  }
}

const loadHotels = async () => {
  try {
    const data = await superAdminService.getAllHotels()
    availableHotels.value = data
  } catch (err) {
    console.error('Failed to load hotels:', err)
  }
}

const handleEdit = async (booking) => {
  editingBooking.value = booking
  form.value = {
    users_id: booking.users_id || '',
    hotels_id: booking.hotels_id || '',
    startDate: booking.startDate ? booking.startDate.split('T')[0] : '',
    endDate: booking.endDate ? booking.endDate.split('T')[0] : '',
    totalPrice: booking.totalPrice || 0,
    status: booking.status || 'pending'
  }
  await Promise.all([loadUsers(), loadHotels()])
  showModal.value = true
}

const handleDelete = (booking) => {
  bookingToDelete.value = booking
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!bookingToDelete.value) return

  try {
    await superAdminService.deleteBooking(bookingToDelete.value.id)
    showToast('Booking deleted successfully', 'success')
    await loadBookings()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete booking', 'error')
  } finally {
    bookingToDelete.value = null
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''

  try {
    await superAdminService.updateBooking(editingBooking.value.id, form.value)
    showToast('Booking updated successfully', 'success')
    closeModal()
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save booking'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingBooking.value = null
  resetForm()
  error.value = ''
}

const resetForm = () => {
  form.value = {
    users_id: '',
    hotels_id: '',
    startDate: '',
    endDate: '',
    totalPrice: 0,
    status: 'pending'
  }
}

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

onMounted(async () => {
  await loadBookings()
})
</script>

<style scoped>
.bookings-page {
  max-width: 1400px;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.status-confirmed {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-cancelled {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-completed {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: rgba(20, 20, 20, 0.95);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-content.large {
  max-width: 800px;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #e5e7eb;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #9ca3af;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background 0.2s;
}

.modal-close:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #d1d5db;
}

.modal-body {
  padding: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #d1d5db;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #e5e7eb;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: rgba(0, 0, 0, 0.5);
}

.error-message {
  background: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(220, 38, 38, 0.4);
  color: #fca5a5;
  padding: 0.875rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.modal-footer {
  padding-top: 1.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: transparent;
  color: #9ca3af;
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  border-color: rgba(102, 126, 234, 0.6);
  color: #d1d5db;
  background: rgba(102, 126, 234, 0.1);
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-edit:hover {
  background: rgba(59, 130, 246, 0.2);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
}
</style>

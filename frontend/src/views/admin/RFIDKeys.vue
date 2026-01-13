<template>
  <AdminLayout>
    <div class="rfid-keys-page">
      <div class="page-header">
        <h1>RFID Key Management</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Add RFID Key
        </button>
      </div>

      <div class="hotel-selector card">
        <h3>Select Hotel</h3>
        <select v-model="selectedHotelId" @change="handleHotelChange" class="hotel-select">
          <option value="">Choose a hotel...</option>
          <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
            {{ hotel.name || hotel.location || `Hotel #${hotel.id}` }}
          </option>
        </select>
      </div>

      <div v-if="selectedHotel">
        <DataTable
          :data="keys"
          :columns="columns"
          :loading="loading"
          search-placeholder="Search by UID..."
          empty-message="No RFID keys found"
          :search-fields="['uid']"
          :on-edit="handleEdit"
          :on-delete="handleDelete"
        >
          <template #cell-status="{ value }">
            <span :class="['status-badge', `status-${value}`]">
              {{ value }}
            </span>
          </template>
          <template #cell-current_booking="{ value }">
            <div v-if="value" class="booking-info">
              <div><strong>Room:</strong> {{ value.room_name }}</div>
              <div><strong>Guest:</strong> {{ value.guest_name }}</div>
              <div class="assigned-date">
                Assigned: {{ formatDate(value.assigned_at) }}
              </div>
            </div>
            <span v-else class="text-muted">-</span>
          </template>
          <template #actions="{ row }">
            <button
              v-if="row.status === 'available'"
              @click="openAssignModal(row)"
              class="btn-icon btn-assign"
              title="Assign"
            >
              🔑
            </button>
            <button
              v-if="row.status === 'assigned' && row.current_booking"
              @click="handleRelease(row)"
              class="btn-icon btn-release"
              title="Release"
            >
              🔓
            </button>
            <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Edit">✏️</button>
            <button
              v-if="row.status !== 'assigned'"
              @click="handleDelete(row)"
              class="btn-icon btn-delete"
              title="Delete"
            >
              🗑️
            </button>
          </template>
        </DataTable>
      </div>

      <!-- Create/Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>{{ editingKey ? 'Edit RFID Key' : 'Create RFID Key' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div v-if="!editingKey" class="form-group">
                <label>Select Hotel *</label>
                <select v-model="form.hotelId" required class="form-select">
                  <option value="">Choose a hotel...</option>
                  <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name || hotel.location || `Hotel #${hotel.id}` }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>UID (RFID Identifier) *</label>
                <input
                  v-model="form.uid"
                  type="text"
                  required
                  placeholder="e.g., F4E4C928"
                  :disabled="editingKey"
                />
                <small class="form-hint">Unique RFID identifier</small>
              </div>

              <div v-if="editingKey" class="form-group">
                <label>Status *</label>
                <select v-model="form.status" required>
                  <option value="available">Available</option>
                  <option value="assigned">Assigned</option>
                </select>
                <small class="form-hint">Note: Only available and assigned statuses are supported</small>
              </div>
              <div v-else class="info-box">
                <p>New RFID keys are created as <strong>Available</strong> by default.</p>
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

      <!-- Assign Modal -->
      <Transition name="modal">
        <div v-if="showAssignModal" class="modal-overlay" @click.self="closeAssignModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>Assign RFID Key</h2>
              <button class="modal-close" @click="closeAssignModal">×</button>
            </div>
            <div class="modal-body">
              <div v-if="assignError" class="error-message">{{ assignError }}</div>

              <div class="key-info">
                <p><strong>UID:</strong> {{ keyToAssign?.uid }}</p>
              </div>

              <div class="form-group">
                <label>Booking *</label>
                <select v-model="assignForm.booking_id" @change="loadBookingRooms" required>
                  <option value="">Select a booking...</option>
                  <option
                    v-for="booking in availableBookings"
                    :key="booking.id"
                    :value="booking.id"
                  >
                    {{ booking.guest_name }} - Room {{ booking.room_name }} ({{ booking.check_in }} to {{ booking.check_out }})
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Room *</label>
                <select v-model="assignForm.room_id" :disabled="!assignForm.booking_id" required>
                  <option value="">Select a room...</option>
                  <option
                    v-for="room in selectedBookingRooms"
                    :key="room.id"
                    :value="room.id"
                  >
                    {{ room.name }}
                  </option>
                </select>
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeAssignModal" class="btn-secondary">Cancel</button>
                <button
                  type="button"
                  @click="confirmAssign"
                  class="btn-primary"
                  :disabled="assigning || !assignForm.booking_id || !assignForm.room_id"
                >
                  {{ assigning ? 'Assigning...' : 'Assign Key' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <ConfirmDialog
        v-model:visible="showDeleteDialog"
        title="Delete RFID Key"
        :message="`Are you sure you want to delete this RFID key? This action cannot be undone.`"
        confirm-text="Delete"
        cancel-text="Cancel"
        confirm-type="danger"
        @confirm="confirmDelete"
      />

      <ConfirmDialog
        v-model:visible="showReleaseDialog"
        title="Release RFID Key"
        :message="`Are you sure you want to release this RFID key from its current assignment?`"
        confirm-text="Release"
        cancel-text="Cancel"
        confirm-type="primary"
        @confirm="confirmRelease"
      />

      <Toast ref="toast" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { rfidKeyService } from '../../services/rfidKeyService'
import { adminService } from '../../services/adminService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const hotels = ref([])
const keys = ref([])
const loading = ref(false)
const showModal = ref(false)
const showAssignModal = ref(false)
const showDeleteDialog = ref(false)
const showReleaseDialog = ref(false)
const editingKey = ref(null)
const keyToAssign = ref(null)
const keyToDelete = ref(null)
const keyToRelease = ref(null)
const saving = ref(false)
const assigning = ref(false)
const error = ref('')
const assignError = ref('')
const toast = ref(null)
const selectedHotelId = ref(null)
const availableBookings = ref([])
const selectedBookingRooms = ref([])

const selectedHotel = computed(() => {
  return hotels.value.find(h => h.id === selectedHotelId.value)
})

const form = ref({
  hotelId: null,
  uid: '',
  status: 'available'
})

const assignForm = ref({
  booking_id: '',
  room_id: ''
})

const columns = [
  { key: 'uid', label: 'UID', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'current_booking', label: 'Current Assignment' },
  { key: 'created_at', label: 'Created', type: 'date' }
]

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

const loadHotels = async () => {
  try {
    const data = await adminService.getHotels()
    hotels.value = data.filter(h => h.user_id === authStore.state.user?.id)
    
    // Auto-select if only one hotel
    if (hotels.value.length === 1) {
      selectedHotelId.value = hotels.value[0].id
      await loadKeys()
    } else {
      loading.value = false
    }
  } catch (err) {
    showToast('Failed to load hotels', 'error')
    loading.value = false
  }
}

const handleHotelChange = async () => {
  if (selectedHotelId.value) {
    await loadKeys()
  } else {
    keys.value = []
    loading.value = false
  }
}

const loadKeys = async () => {
  if (!selectedHotelId.value) {
    loading.value = false
    keys.value = []
    return
  }

  loading.value = true
  try {
    const response = await rfidKeyService.getKeys({ hotel_id: selectedHotelId.value })
    // Handle response structure
    if (Array.isArray(response)) {
      keys.value = response
    } else if (response && Array.isArray(response.keys)) {
      keys.value = response.keys
    } else if (response && response.keys) {
      keys.value = response.keys
    } else {
      keys.value = []
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to load RFID keys', 'error')
    keys.value = []
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingKey.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = (key) => {
  editingKey.value = key
  form.value = {
    uid: key.uid,
    status: key.isUsed ? 'assigned' : 'available'
  }
  showModal.value = true
}

const handleDelete = (key) => {
  keyToDelete.value = key
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!keyToDelete.value) return

  try {
    await rfidKeyService.deleteKey(keyToDelete.value.id)
    showToast('RFID key deleted successfully', 'success')
    await loadKeys()
  } catch (err) {
    showToast(err.response?.data?.error || 'Failed to delete RFID key', 'error')
  } finally {
    keyToDelete.value = null
  }
}

const openAssignModal = async (key) => {
  keyToAssign.value = key
  assignError.value = ''
  resetAssignForm()

  try {
    const response = await rfidKeyService.getAvailableBookings()
    availableBookings.value = response.bookings || response || []
  } catch (err) {
    assignError.value = err.response?.data?.message || 'Failed to load bookings'
  }

  showAssignModal.value = true
}

const loadBookingRooms = () => {
  const booking = availableBookings.value.find(b => b.id === assignForm.value.booking_id)
  if (booking && booking.room_id) {
    selectedBookingRooms.value = [{ id: booking.room_id, name: booking.room_name }]
    assignForm.value.room_id = booking.room_id
  } else {
    selectedBookingRooms.value = []
    assignForm.value.room_id = ''
  }
}

const confirmAssign = async () => {
  if (!keyToAssign.value || !assignForm.value.booking_id || !assignForm.value.room_id) return

  assigning.value = true
  assignError.value = ''

  try {
    await rfidKeyService.assignKey(
      keyToAssign.value.id,
      assignForm.value.booking_id,
      assignForm.value.room_id
    )
    showToast('RFID key assigned successfully', 'success')
    closeAssignModal()
    await loadKeys()
  } catch (err) {
    assignError.value = err.response?.data?.error || 'Failed to assign RFID key'
  } finally {
    assigning.value = false
  }
}

const handleRelease = (key) => {
  keyToRelease.value = key
  showReleaseDialog.value = true
}

const confirmRelease = async () => {
  if (!keyToRelease.value) return

  try {
    await rfidKeyService.releaseKey(keyToRelease.value.id)
    showToast('RFID key released successfully', 'success')
    await loadKeys()
  } catch (err) {
    showToast(err.response?.data?.error || 'Failed to release RFID key', 'error')
  } finally {
    keyToRelease.value = null
  }
}

const handleSubmit = async () => {
  if (!editingKey.value && !form.value.hotelId) {
    showToast('Please select a hotel', 'warning')
    return
  }

  saving.value = true
  error.value = ''

  try {
    if (editingKey.value) {
      const updateData = { uid: form.value.uid }
      const newStatus = form.value.status
      const currentStatus = editingKey.value.isUsed ? 'assigned' : 'available'
      if (newStatus !== currentStatus) {
        updateData.status = newStatus
      }
      await rfidKeyService.updateKey(editingKey.value.id, updateData)
      showToast('RFID key updated successfully', 'success')
    } else {
      await rfidKeyService.createKey({
        uid: form.value.uid,
        hotel_id: form.value.hotelId
      })
      showToast('RFID key created successfully', 'success')
    }
    closeModal()
    await loadKeys()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to save RFID key'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingKey.value = null
  resetForm()
  error.value = ''
}

const closeAssignModal = () => {
  showAssignModal.value = false
  keyToAssign.value = null
  resetAssignForm()
  assignError.value = ''
}

const resetForm = () => {
  form.value = {
    hotelId: null,
    uid: '',
    status: 'available'
  }
}

const resetAssignForm = () => {
  assignForm.value = {
    booking_id: '',
    room_id: ''
  }
  selectedBookingRooms.value = []
  assignError.value = ''
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

const handleHotelsUpdated = async () => {
  await loadHotels()
}

onMounted(async () => {
  try {
    await loadHotels()
  } catch (err) {
    console.error('Error loading RFID keys page:', err)
  }
  window.addEventListener('hotels-updated', handleHotelsUpdated)
})

onUnmounted(() => {
  window.removeEventListener('hotels-updated', handleHotelsUpdated)
})
</script>

<style scoped>
.rfid-keys-page {
  max-width: 1400px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
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

.hotel-selector {
  margin-bottom: 2rem;
  padding: 1.5rem;
}

.hotel-selector h3 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  color: #2c3e50;
}

.hotel-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-available {
  background-color: #d4edda;
  color: #155724;
}

.status-assigned {
  background-color: #cce5ff;
  color: #004085;
}

.booking-info {
  font-size: 0.9rem;
}

.booking-info div {
  margin-bottom: 0.25rem;
}

.assigned-date {
  color: #7f8c8d;
  font-size: 0.85rem;
}

.text-muted {
  color: #7f8c8d;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.btn-icon:hover {
  background-color: #f0f0f0;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #2c3e50;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #7f8c8d;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background-color: #ecf0f1;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #2c3e50;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
}

.form-group input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.form-hint {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.error-message {
  background-color: #f8d7da;
  color: #721c24;
  padding: 0.75rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.info-box {
  background-color: #e8f4f8;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.info-box p {
  margin: 0;
  color: #2c3e50;
  font-size: 0.9rem;
}

.key-info {
  background-color: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.key-info p {
  margin: 0.5rem 0;
}

.modal-footer {
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background-color: #ecf0f1;
  color: #2c3e50;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: #d5dbdb;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.9);
}
</style>

<template>
  <SuperAdminLayout>
    <div class="rooms-page">
      <div class="page-header">
        <h1>Rooms Management</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Add Room
        </button>
      </div>

      <DataTable
        :data="rooms"
        :columns="columns"
        :loading="loading"
        search-placeholder="Search rooms..."
        empty-message="No rooms found"
        :search-fields="['name', 'description']"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
      >
        <template #cell-pricePerNight="{ value }">
          €{{ parseFloat(value || 0).toFixed(2) }}
        </template>
        <template #cell-capacity="{ value }">
          {{ value }} guests
        </template>
        <template #cell-hotel="{ value }">
          {{ value?.name || 'N/A' }}
        </template>
        <template #actions="{ row }">
          <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Edit">✏️</button>
          <button @click="handleDelete(row)" class="btn-icon btn-delete" title="Delete">🗑️</button>
        </template>
      </DataTable>

      <!-- Create/Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content large">
            <div class="modal-header">
              <h2>{{ editingRoom ? 'Edit Room' : 'Create Room' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Hotel *</label>
                <select v-model="form.hotels_id" required>
                  <option value="">Select hotel...</option>
                  <option v-for="hotel in availableHotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name }} - {{ hotel.location }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Room Name *</label>
                <input v-model="form.name" type="text" required placeholder="Enter room name" />
              </div>

              <div class="form-group">
                <label>Description</label>
                <textarea v-model="form.description" rows="3" placeholder="Enter room description"></textarea>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Price Per Night (€) *</label>
                  <input v-model.number="form.pricePerNight" type="number" step="0.01" min="0" required />
                </div>
                <div class="form-group">
                  <label>Base Price (€) *</label>
                  <input v-model.number="form.basePrice" type="number" step="0.01" min="0" required />
                </div>
              </div>

              <div class="form-group">
                <label>Capacity (guests) *</label>
                <input v-model.number="form.capacity" type="number" min="1" required />
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
        title="Delete Room"
        :message="`Are you sure you want to delete this room? This action cannot be undone.`"
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

const rooms = ref([])
const availableHotels = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingRoom = ref(null)
const roomToDelete = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)

const form = ref({
  hotels_id: '',
  name: '',
  description: '',
  pricePerNight: 0,
  basePrice: 0,
  capacity: 1
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Room Name', sortable: true },
  { key: 'hotel', label: 'Hotel' },
  { key: 'pricePerNight', label: 'Price/Night', sortable: true },
  { key: 'basePrice', label: 'Base Price', sortable: true },
  { key: 'capacity', label: 'Capacity', sortable: true },
  { key: 'description', label: 'Description' }
]

const loadRooms = async () => {
  loading.value = true
  try {
    const data = await superAdminService.getAllRooms()
    rooms.value = data
  } catch (err) {
    showToast('Failed to load rooms', 'error')
  } finally {
    loading.value = false
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

const openCreateModal = async () => {
  editingRoom.value = null
  resetForm()
  await loadHotels()
  showModal.value = true
}

const handleEdit = async (room) => {
  editingRoom.value = room
  form.value = {
    hotels_id: room.hotels_id || '',
    name: room.name || '',
    description: room.description || '',
    pricePerNight: room.pricePerNight || 0,
    basePrice: room.basePrice || 0,
    capacity: room.capacity || 1
  }
  await loadHotels()
  showModal.value = true
}

const handleDelete = (room) => {
  roomToDelete.value = room
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!roomToDelete.value) return

  try {
    await superAdminService.deleteRoom(roomToDelete.value.id)
    showToast('Room deleted successfully', 'success')
    await loadRooms()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete room', 'error')
  } finally {
    roomToDelete.value = null
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''

  try {
    if (editingRoom.value) {
      await superAdminService.updateRoom(editingRoom.value.id, form.value)
      showToast('Room updated successfully', 'success')
    } else {
      await superAdminService.createRoom(form.value)
      showToast('Room created successfully', 'success')
    }
    closeModal()
    await loadRooms()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save room'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingRoom.value = null
  resetForm()
  error.value = ''
}

const resetForm = () => {
  form.value = {
    hotels_id: '',
    name: '',
    description: '',
    pricePerNight: 0,
    basePrice: 0,
    capacity: 1
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
  await loadRooms()
})
</script>

<style scoped>
.rooms-page {
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
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
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

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #e5e7eb;
  font-size: 0.95rem;
  transition: all 0.2s;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
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

<template>
  <AdminLayout>
    <div class="rooms-page">
      <div class="page-header">
        <h1>Rooms Management</h1>
        <button @click="openCreateModal" class="btn-primary" :disabled="!selectedHotel">
          <span>➕</span> Add Room
        </button>
      </div>

      <div v-if="!selectedHotel" class="hotel-selector card">
        <h3>Select Hotel</h3>
        <select v-model="selectedHotelId" @change="loadRooms" class="hotel-select">
          <option value="">Choose a hotel...</option>
          <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
            {{ hotel.location || hotel.user?.name || `Hotel #${hotel.id}` }}
          </option>
        </select>
      </div>

      <div v-else>
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
          <template #actions="{ row }">
            <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Edit">✏️</button>
            <button @click="handleDelete(row)" class="btn-icon btn-delete" title="Delete">🗑️</button>
          </template>
        </DataTable>
      </div>

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
                <label>Room Name/Number *</label>
                <input v-model="form.name" type="text" required placeholder="e.g., Room 101" />
              </div>

              <div class="form-group">
                <label>Description *</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  required
                  placeholder="Enter room description"
                ></textarea>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Capacity (guests) *</label>
                  <input v-model.number="form.capacity" type="number" min="1" required />
                </div>
                <div class="form-group">
                  <label>Price Per Night (€) *</label>
                  <input v-model.number="form.pricePerNight" type="number" min="0" step="0.01" required />
                </div>
              </div>

              <div class="form-group">
                <label>Base Price (€) *</label>
                <input v-model.number="form.basePrice" type="number" min="0" step="0.01" required />
              </div>

              <div class="form-group">
                <label>Room Images</label>
                <ImageUpload
                  v-model="form.images"
                  :max-files="10"
                  @upload="handleImageUpload"
                />
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
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import ImageUpload from '../../components/ImageUpload.vue'
import Toast from '../../components/Toast.vue'
import { adminService } from '../../services/adminService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const hotels = ref([])
const rooms = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingRoom = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)
const roomToDelete = ref(null)
const selectedHotelId = ref(null)

const selectedHotel = computed(() => {
  return hotels.value.find(h => h.id === selectedHotelId.value)
})

const form = ref({
  name: '',
  description: '',
  capacity: 1,
  pricePerNight: 0,
  basePrice: 0,
  images: []
})

const columns = [
  { key: 'name', label: 'Room Name', sortable: true },
  { key: 'description', label: 'Description' },
  { key: 'capacity', label: 'Capacity', sortable: true },
  { key: 'pricePerNight', label: 'Price/Night', sortable: true, type: 'currency' }
]

const loadHotels = async () => {
  try {
    const data = await adminService.getHotels()
    hotels.value = data.filter(h => h.user_id === authStore.state.user?.id)
    
    // Auto-select if only one hotel
    if (hotels.value.length === 1) {
      selectedHotelId.value = hotels.value[0].id
      await loadRooms()
    }
  } catch (err) {
    showToast('Failed to load hotels', 'error')
  }
}

const loadRooms = async () => {
  if (!selectedHotelId.value) return

  loading.value = true
  try {
    rooms.value = await adminService.getRoomsByHotelId(selectedHotelId.value)
  } catch (err) {
    showToast('Failed to load rooms', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  if (!selectedHotel.value) {
    showToast('Please select a hotel first', 'warning')
    return
  }
  editingRoom.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = async (room) => {
  editingRoom.value = room
  form.value = {
    name: room.name || '',
    description: room.description || '',
    capacity: room.capacity || 1,
    pricePerNight: room.pricePerNight || 0,
    basePrice: room.basePrice || 0,
    images: []
  }

  // Load room images
  try {
    const images = await adminService.getRoomImages(room.id)
    form.value.images = images.map(img => ({ id: img.id, url: img.url }))
  } catch (err) {
    console.error('Failed to load room images:', err)
  }

  showModal.value = true
}

const handleDelete = (room) => {
  roomToDelete.value = room
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!roomToDelete.value) return

  try {
    await adminService.deleteRoom(roomToDelete.value.id)
    showToast('Room deleted successfully', 'success')
    await loadRooms()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete room', 'error')
  } finally {
    roomToDelete.value = null
  }
}

const handleImageUpload = async (imageObj) => {
  if (!selectedHotel.value) return

  try {
    const roomId = editingRoom.value?.id
    if (roomId) {
      // Upload image and link to room
      const result = await adminService.uploadImage(imageObj.file, [roomId])
      imageObj.id = result.image.id
      imageObj.url = result.image.url
      imageObj.preview = result.image.url
    }
  } catch (err) {
    showToast('Failed to upload image', 'error')
    throw err
  }
}

const handleSubmit = async () => {
  if (!selectedHotel.value) {
    showToast('Please select a hotel', 'warning')
    return
  }

  saving.value = true
  error.value = ''

  try {
    if (editingRoom.value) {
      await adminService.updateRoom(editingRoom.value.id, {
        name: form.value.name,
        description: form.value.description,
        capacity: form.value.capacity,
        pricePerNight: form.value.pricePerNight,
        basePrice: form.value.basePrice
      })
      showToast('Room updated successfully', 'success')
    } else {
      await adminService.createRoom(selectedHotel.value.id, {
        name: form.value.name,
        description: form.value.description,
        capacity: form.value.capacity,
        pricePerNight: form.value.pricePerNight,
        basePrice: form.value.basePrice
      })
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
    name: '',
    description: '',
    capacity: 1,
    pricePerNight: 0,
    basePrice: 0,
    images: []
  }
}

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

onMounted(() => {
  loadHotels()
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
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-content.large {
  max-width: 900px;
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
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
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

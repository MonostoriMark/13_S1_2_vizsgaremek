<template>
  <AdminLayout>
    <div class="hotels-page">
      <div class="page-header">
        <h1>Hotels Management</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Add Hotel
        </button>
      </div>

      <DataTable
        :data="hotels"
        :columns="columns"
        :loading="loading"
        search-placeholder="Search hotels..."
        empty-message="No hotels found"
        :search-fields="['location', 'type', 'description']"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
      >
        <template #cell-starRating="{ value }">
          <span class="stars">{{ '★'.repeat(value || 0) }}</span>
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
              <h2>{{ editingHotel ? 'Edit Hotel' : 'Create Hotel' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Hotel Name *</label>
                <input v-model="form.name" type="text" required placeholder="Enter hotel name" />
              </div>

              <div class="form-group">
                <label>Location/Address *</label>
                <input v-model="form.location" type="text" required placeholder="Enter location" />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Type</label>
                  <select v-model="form.type">
                    <option value="">Select type</option>
                    <option value="hotel">Hotel</option>
                    <option value="resort">Resort</option>
                    <option value="apartment">Apartment</option>
                    <option value="hostel">Hostel</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Star Rating</label>
                  <select v-model.number="form.starRating">
                    <option :value="null">No rating</option>
                    <option :value="1">1 Star</option>
                    <option :value="2">2 Stars</option>
                    <option :value="3">3 Stars</option>
                    <option :value="4">4 Stars</option>
                    <option :value="5">5 Stars</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>Description</label>
                <textarea
                  v-model="form.description"
                  rows="4"
                  placeholder="Enter hotel description"
                ></textarea>
              </div>

              <div class="form-group">
                <label>Status</label>
                <label class="switch">
                  <input v-model="form.active" type="checkbox" />
                  <span class="slider"></span>
                  <span class="switch-label">{{ form.active ? 'Active' : 'Inactive' }}</span>
                </label>
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
        title="Delete Hotel"
        :message="`Are you sure you want to delete this hotel? This action cannot be undone.`"
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
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { adminService } from '../../services/adminService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const hotels = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingHotel = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)
const hotelToDelete = ref(null)

const form = ref({
  name: '',
  location: '',
  type: '',
  starRating: null,
  description: '',
  active: true
})

const columns = [
  { key: 'location', label: 'Location', sortable: true },
  { key: 'type', label: 'Type', sortable: true },
  { key: 'starRating', label: 'Rating', sortable: true },
  { key: 'description', label: 'Description' }
]

const loadHotels = async () => {
  loading.value = true
  try {
    const data = await adminService.getHotels()
    // Filter to show only hotels belonging to the current user
    hotels.value = data.filter(h => h.user_id === authStore.state.user?.id)
  } catch (err) {
    showToast('Failed to load hotels', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingHotel.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = (hotel) => {
  editingHotel.value = hotel
  form.value = {
    name: hotel.user?.name || '',
    location: hotel.location || '',
    type: hotel.type || '',
    starRating: hotel.starRating || null,
    description: hotel.description || '',
    active: true // Note: active field may need to be added to backend
  }
  showModal.value = true
}

const handleDelete = (hotel) => {
  hotelToDelete.value = hotel
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!hotelToDelete.value) return

  try {
    await adminService.deleteHotel(hotelToDelete.value.id)
    showToast('Hotel deleted successfully', 'success')
    await loadHotels()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete hotel', 'error')
  } finally {
    hotelToDelete.value = null
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''

  try {
    if (editingHotel.value) {
      await adminService.updateHotel(editingHotel.value.id, {
        name: form.value.name,
        location: form.value.location,
        type: form.value.type,
        starRating: form.value.starRating,
        description: form.value.description
      })
      showToast('Hotel updated successfully', 'success')
    } else {
      // Note: Hotel creation might need a different endpoint
      // For now, we'll show an error if the endpoint doesn't exist
      showToast('Hotel creation endpoint not available', 'warning')
    }
    closeModal()
    await loadHotels()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save hotel'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingHotel.value = null
  resetForm()
  error.value = ''
}

const resetForm = () => {
  form.value = {
    name: '',
    location: '',
    type: '',
    starRating: null,
    description: '',
    active: true
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
.hotels-page {
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

.modal-content.large {
  max-width: 800px;
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

.switch {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.switch input[type="checkbox"] {
  width: 48px;
  height: 24px;
  appearance: none;
  background-color: #ccc;
  border-radius: 12px;
  position: relative;
  cursor: pointer;
  transition: background-color 0.3s;
}

.switch input[type="checkbox"]:checked {
  background-color: #667eea;
}

.switch input[type="checkbox"]::before {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: white;
  top: 2px;
  left: 2px;
  transition: transform 0.3s;
}

.switch input[type="checkbox"]:checked::before {
  transform: translateX(24px);
}

.switch-label {
  font-weight: 500;
  color: #2c3e50;
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

.stars {
  color: #f39c12;
  font-size: 1.1rem;
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
